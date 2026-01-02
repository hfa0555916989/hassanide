/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { Emitter } from '../../../../base/common/event.js';
import { Disposable } from '../../../../base/common/lifecycle.js';
import { RawContextKey, IContextKeyService } from '../../../../platform/contextkey/common/contextkey.js';
import { ILogService } from '../../../../platform/log/common/log.js';
import { ISecretStorageService } from '../../../../platform/secrets/common/secrets.js';
import { ILicenseFeatureFlags, ILicenseService } from '../common/licensing.js';
import { LICENSE_BASE_URL, LICENSE_ENDPOINTS } from '../../../../platform/licensing/common/licensing.js';

const licenseTokenKey = 'hassanide.licenseToken';
const licenseActiveContextKey = new RawContextKey<boolean>('hassanide.license.active', false);
const paidFeaturesEnabledContextKey = new RawContextKey<boolean>('hassanide.paidFeaturesEnabled', false);

export class LicenseService extends Disposable implements ILicenseService {
	declare readonly _serviceBrand: undefined;

	private readonly onDidChangeLicenseEmitter = this._register(new Emitter<boolean>());
	readonly onDidChangeLicense = this.onDidChangeLicenseEmitter.event;

	private readonly licenseActiveKey = licenseActiveContextKey.bindTo(this.contextKeyService);
	private readonly paidFeaturesEnabledKey = paidFeaturesEnabledContextKey.bindTo(this.contextKeyService);
	private _isActive = false;

	constructor(
		@ISecretStorageService private readonly secretStorageService: ISecretStorageService,
		@IContextKeyService private readonly contextKeyService: IContextKeyService,
		@ILogService private readonly logService: ILogService,
	) {
		super();

		this._register(this.secretStorageService.onDidChangeSecret(key => {
			if (key === licenseTokenKey) {
				this.refreshState().catch(error => this.logService.error('Failed to refresh license state', error));
			}
		}));

		this.refreshState().catch(error => this.logService.error('Failed to initialize license state', error));
		this.logService.trace('License endpoints configured', LICENSE_BASE_URL, LICENSE_ENDPOINTS);
	}

	async isActive(): Promise<boolean> {
		if (!this._isActive) {
			await this.refreshState();
		}
		return this._isActive;
	}

	async activate(token: string): Promise<void> {
		await this.secretStorageService.set(licenseTokenKey, token);
		await this.refreshState();
	}

	async clear(): Promise<void> {
		await this.secretStorageService.delete(licenseTokenKey);
		await this.refreshState();
	}

	async getFeatureFlags(): Promise<ILicenseFeatureFlags> {
		const active = await this.isActive();
		return { paidFeaturesEnabled: active };
	}

	private async refreshState(): Promise<void> {
		const token = await this.secretStorageService.get(licenseTokenKey);
		const isActive = Boolean(token);
		this._isActive = isActive;
		this.licenseActiveKey.set(isActive);
		this.paidFeaturesEnabledKey.set(isActive);
		this.onDidChangeLicenseEmitter.fire(isActive);
	}
}
