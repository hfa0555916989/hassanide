/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { Disposable } from '../../../base/common/lifecycle.js';
import { Emitter, Event } from '../../../base/common/event.js';
import {
	ILicenseService,
	ILicenseStatus,
	IActivationResult,
	IStoredLicense,
	ILicenseDevice,
	LicensePlan,
	LicenseFeature,
	FEATURE_PLAN_MAP,
	planIncludesFeatures,
	DEFAULT_LICENSE_API_CONFIG
} from '../common/license.js';
import { IStorageService, StorageScope, StorageTarget } from '../../storage/common/storage.js';
import { ILogService } from '../../log/common/log.js';

const LICENSE_STORAGE_KEY = 'hassanide.license';
const OFFLINE_GRACE_DAYS = 7;

/**
 * API Response interfaces
 */
interface IApiActivateResponse {
	valid: boolean;
	message?: string;
	token?: string;
	plan?: string;
	features?: string[];
	expires_at?: string;
	user?: { email: string; name: string };
}

interface IApiValidateResponse {
	valid: boolean;
	error?: string;
	message?: string;
	plan?: string;
	plan_name?: string;
	features?: string[];
	expires_at?: string;
	days_remaining?: number;
	max_devices?: number;
	active_devices?: number;
	devices?: Array<{
		machine_id: string;
		machine_name: string;
		added_at: string;
		last_seen: string;
	}>;
	user?: { email: string; name: string };
	offline_grace_days?: number;
}

interface IApiRemoveDeviceResponse {
	success: boolean;
	message?: string;
}

/**
 * License service implementation for browser/renderer process
 */
export class LicenseService extends Disposable implements ILicenseService {
	declare readonly _serviceBrand: undefined;

	private readonly _onDidChangeLicenseStatus = this._register(new Emitter<ILicenseStatus>());
	readonly onDidChangeLicenseStatus: Event<ILicenseStatus> = this._onDidChangeLicenseStatus.event;

	private _currentStatus: ILicenseStatus | undefined;
	private _storedLicense: IStoredLicense | undefined;
	private _machineId: string | undefined;

	private readonly _apiConfig = DEFAULT_LICENSE_API_CONFIG;

	constructor(
		@IStorageService private readonly storageService: IStorageService,
		@ILogService private readonly logService: ILogService
	) {
		super();

		this._initializeMachineId();
		this._loadStoredLicense();
	}

	private _initializeMachineId(): void {
		// Generate a simple machine ID for browser context
		let machineId = this.storageService.get('hassanide.machineId', StorageScope.APPLICATION);
		if (!machineId) {
			machineId = 'browser-' + Date.now().toString(36) + '-' + Math.random().toString(36).substring(2);
			this.storageService.store('hassanide.machineId', machineId, StorageScope.APPLICATION, StorageTarget.MACHINE);
		}
		this._machineId = machineId;
	}

	private _loadStoredLicense(): void {
		try {
			const stored = this.storageService.get(LICENSE_STORAGE_KEY, StorageScope.APPLICATION);
			if (stored) {
				this._storedLicense = JSON.parse(stored);
				this.logService.info('[LicenseService] Loaded stored license:', this._storedLicense?.plan);
			}
		} catch (error) {
			this.logService.error('[LicenseService] Failed to load stored license:', error);
		}
	}

	private _saveStoredLicense(license: IStoredLicense): void {
		try {
			this.storageService.store(
				LICENSE_STORAGE_KEY,
				JSON.stringify(license),
				StorageScope.APPLICATION,
				StorageTarget.MACHINE
			);
			this._storedLicense = license;
		} catch (error) {
			this.logService.error('[LicenseService] Failed to save license:', error);
		}
	}

	private _clearStoredLicense(): void {
		this.storageService.remove(LICENSE_STORAGE_KEY, StorageScope.APPLICATION);
		this._storedLicense = undefined;
	}

	async getLicenseStatus(): Promise<ILicenseStatus> {
		if (this._currentStatus) {
			return this._currentStatus;
		}

		if (!this._storedLicense) {
			return this._getFreePlanStatus();
		}

		return this._storedLicenseToStatus(this._storedLicense, false);
	}

	async activateLicense(licenseKey: string): Promise<IActivationResult> {
		const formattedKey = this._formatLicenseKey(licenseKey);

		try {
			const response = await this._callApi<IApiActivateResponse>('activate', {
				license_key: formattedKey,
				machine_id: this._machineId,
				machine_name: 'HassanIDE Web'
			});

			if (!response.valid) {
				return {
					success: false,
					message: response.message || 'License activation failed'
				};
			}

			// Save the license
			const storedLicense: IStoredLicense = {
				licenseKey: formattedKey,
				token: response.token,
				plan: (response.plan as LicensePlan) || LicensePlan.Free,
				features: response.features || [],
				expiresAt: response.expires_at || null,
				lastValidated: new Date().toISOString(),
				user: response.user
			};

			this._saveStoredLicense(storedLicense);
			this._currentStatus = this._storedLicenseToStatus(storedLicense, false);
			this._onDidChangeLicenseStatus.fire(this._currentStatus);

			return {
				success: true,
				message: 'License activated successfully',
				plan: storedLicense.plan,
				features: storedLicense.features,
				expiresAt: storedLicense.expiresAt || undefined
			};
		} catch (error) {
			this.logService.error('[LicenseService] Activation failed:', error);
			return {
				success: false,
				message: 'Failed to activate license. Please check your internet connection.'
			};
		}
	}

	async deactivateLicense(): Promise<boolean> {
		if (this._storedLicense && this._machineId) {
			try {
				await this._callApi<IApiRemoveDeviceResponse>('remove_device', {
					license_key: this._storedLicense.licenseKey,
					machine_id: this._machineId
				});
			} catch (error) {
				this.logService.warn('[LicenseService] Failed to notify server of deactivation:', error);
			}
		}

		this._clearStoredLicense();
		this._currentStatus = this._getFreePlanStatus();
		this._onDidChangeLicenseStatus.fire(this._currentStatus);
		return true;
	}

	hasFeature(feature: LicenseFeature): boolean {
		const currentPlan = this.getCurrentPlan();
		const requiredPlan = FEATURE_PLAN_MAP[feature];
		return planIncludesFeatures(currentPlan, requiredPlan);
	}

	getCurrentPlan(): LicensePlan {
		if (!this._storedLicense || !this.isLicenseValid()) {
			return LicensePlan.Free;
		}
		return this._storedLicense.plan;
	}

	getAvailableFeatures(): readonly string[] {
		if (!this._storedLicense) {
			return this._getFreePlanFeatures();
		}
		return this._storedLicense.features;
	}

	isLicenseValid(): boolean {
		if (!this._storedLicense) {
			return false;
		}

		// Check expiration
		if (this._storedLicense.expiresAt) {
			const expiresAt = new Date(this._storedLicense.expiresAt);
			if (expiresAt < new Date()) {
				return false;
			}
		}

		// Check offline grace period
		const lastValidated = new Date(this._storedLicense.lastValidated);
		const daysSinceValidation = (Date.now() - lastValidated.getTime()) / (1000 * 60 * 60 * 24);

		if (daysSinceValidation > OFFLINE_GRACE_DAYS) {
			return false;
		}

		return true;
	}

	getRequiredPlanForFeature(feature: LicenseFeature): LicensePlan {
		return FEATURE_PLAN_MAP[feature];
	}

	async refreshLicenseStatus(): Promise<ILicenseStatus> {
		if (!this._storedLicense) {
			this._currentStatus = this._getFreePlanStatus();
			return this._currentStatus;
		}

		try {
			const response = await this._callApi<IApiValidateResponse>('validate', {
				license_key: this._storedLicense.licenseKey,
				machine_id: this._machineId,
				machine_name: 'HassanIDE Web'
			});

			if (!response.valid) {
				this._clearStoredLicense();
				this._currentStatus = {
					...this._getFreePlanStatus(),
					error: response.error,
					errorMessage: response.message
				};
				this._onDidChangeLicenseStatus.fire(this._currentStatus);
				return this._currentStatus;
			}

			// Update stored license
			const updatedLicense: IStoredLicense = {
				...this._storedLicense,
				plan: (response.plan as LicensePlan) || this._storedLicense.plan,
				features: response.features || this._storedLicense.features,
				expiresAt: response.expires_at || this._storedLicense.expiresAt,
				lastValidated: new Date().toISOString(),
				user: response.user || this._storedLicense.user
			};

			this._saveStoredLicense(updatedLicense);

			const devices: ILicenseDevice[] = (response.devices || []).map(d => ({
				machineId: d.machine_id,
				machineName: d.machine_name,
				addedAt: d.added_at,
				lastSeen: d.last_seen
			}));

			this._currentStatus = {
				isValid: true,
				plan: updatedLicense.plan,
				planName: response.plan_name || updatedLicense.plan,
				features: updatedLicense.features,
				expiresAt: updatedLicense.expiresAt,
				daysRemaining: response.days_remaining ?? null,
				maxDevices: response.max_devices ?? 1,
				activeDevices: response.active_devices ?? 1,
				devices,
				user: updatedLicense.user,
				offlineGraceDays: response.offline_grace_days ?? OFFLINE_GRACE_DAYS,
				isOffline: false
			};

			this._onDidChangeLicenseStatus.fire(this._currentStatus);
			return this._currentStatus;
		} catch (error) {
			this.logService.warn('[LicenseService] Validation failed, using offline mode:', error);

			if (this.isLicenseValid()) {
				this._currentStatus = this._storedLicenseToStatus(this._storedLicense, true);
				return this._currentStatus;
			}

			this._currentStatus = this._getFreePlanStatus();
			this._onDidChangeLicenseStatus.fire(this._currentStatus);
			return this._currentStatus;
		}
	}

	async removeDevice(machineId: string): Promise<boolean> {
		if (!this._storedLicense) {
			return false;
		}

		try {
			const response = await this._callApi<IApiRemoveDeviceResponse>('remove_device', {
				license_key: this._storedLicense.licenseKey,
				machine_id: machineId
			});

			if (response.success) {
				await this.refreshLicenseStatus();
				return true;
			}

			return false;
		} catch (error) {
			this.logService.error('[LicenseService] Failed to remove device:', error);
			return false;
		}
	}

	getStoredLicense(): IStoredLicense | undefined {
		return this._storedLicense;
	}

	private async _callApi<T>(action: string, data: Record<string, unknown>): Promise<T> {
		const url = `${this._apiConfig.apiUrl}${this._apiConfig.validateEndpoint}`;

		const response = await fetch(url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({ action, ...data })
		});

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}`);
		}

		return response.json() as Promise<T>;
	}

	private _formatLicenseKey(key: string): string {
		const cleaned = key.replace(/\s/g, '').toUpperCase();

		if (/^[A-Z]{3,4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/.test(cleaned)) {
			return cleaned;
		}

		const alphanumeric = cleaned.replace(/[^A-Z0-9]/g, '');
		if (alphanumeric.length >= 16) {
			const parts = [];
			for (let i = 0; i < 16; i += 4) {
				parts.push(alphanumeric.substring(i, i + 4));
			}
			return parts.join('-');
		}

		return cleaned;
	}

	private _getFreePlanStatus(): ILicenseStatus {
		return {
			isValid: false,
			plan: LicensePlan.Free,
			planName: 'Starter',
			features: this._getFreePlanFeatures(),
			expiresAt: null,
			daysRemaining: null,
			maxDevices: 1,
			activeDevices: 0,
			devices: [],
			offlineGraceDays: 0,
			isOffline: false
		};
	}

	private _getFreePlanFeatures(): readonly string[] {
		return [
			LicenseFeature.BasicEditor,
			LicenseFeature.SyntaxHighlighting,
			LicenseFeature.FileExplorer,
			LicenseFeature.Terminal,
			LicenseFeature.GitBasic
		];
	}

	private _storedLicenseToStatus(license: IStoredLicense, isOffline: boolean): ILicenseStatus {
		return {
			isValid: this.isLicenseValid(),
			plan: license.plan,
			planName: license.plan,
			features: license.features,
			expiresAt: license.expiresAt,
			daysRemaining: this._calculateDaysRemaining(license.expiresAt),
			maxDevices: license.plan === LicensePlan.Free ? 1 : license.plan === LicensePlan.Pro ? 3 : 10,
			activeDevices: 1,
			devices: [],
			user: license.user,
			offlineGraceDays: OFFLINE_GRACE_DAYS,
			isOffline
		};
	}

	private _calculateDaysRemaining(expiresAt: string | null): number | null {
		if (!expiresAt) {
			return null;
		}

		const expiration = new Date(expiresAt);
		const now = new Date();
		const diff = expiration.getTime() - now.getTime();

		if (diff <= 0) {
			return 0;
		}

		return Math.ceil(diff / (1000 * 60 * 60 * 24));
	}
}
