/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { createDecorator } from '../../instantiation/common/instantiation.js';
import { Event } from '../../../base/common/event.js';
import { IDisposable } from '../../../base/common/lifecycle.js';

/**
 * License plans available in HassanIDE
 */
export const enum LicensePlan {
	Free = 'starter',
	Pro = 'pro',
	Team = 'teams'
}

/**
 * Features that can be gated by license
 */
export const enum LicenseFeature {
	// Free features
	BasicEditor = 'basic_editor',
	SyntaxHighlighting = 'syntax_highlighting',
	FileExplorer = 'file_explorer',
	Terminal = 'terminal',
	GitBasic = 'git_basic',

	// Pro features
	AIAssistant = 'ai_assistant',
	AdvancedDebugging = 'advanced_debugging',
	Templates = 'templates',
	CloudSync = 'cloud_sync',
	ExtensionsUnlimited = 'extensions_unlimited',
	HassanPanel = 'hassan_panel',
	CodeSnippets = 'code_snippets',
	MultiCursor = 'multi_cursor',

	// Team features
	TeamCollaboration = 'team_collaboration',
	SharedWorkspaces = 'shared_workspaces',
	TeamAnalytics = 'team_analytics',
	AdminDashboard = 'admin_dashboard',
	SSOIntegration = 'sso_integration',
	PrioritySupport = 'priority_support',
	CodeReview = 'code_review'
}

/**
 * Device information for license activation
 */
export interface ILicenseDevice {
	readonly machineId: string;
	readonly machineName: string;
	readonly addedAt: string;
	readonly lastSeen: string;
}

/**
 * License status returned from validation
 */
export interface ILicenseStatus {
	readonly isValid: boolean;
	readonly plan: LicensePlan;
	readonly planName: string;
	readonly features: readonly string[];
	readonly expiresAt: string | null;
	readonly daysRemaining: number | null;
	readonly maxDevices: number;
	readonly activeDevices: number;
	readonly devices: readonly ILicenseDevice[];
	readonly user?: {
		readonly email: string;
		readonly name: string;
	};
	readonly offlineGraceDays: number;
	readonly isOffline?: boolean;
	readonly error?: string;
	readonly errorMessage?: string;
}

/**
 * Result of license activation
 */
export interface IActivationResult {
	readonly success: boolean;
	readonly message: string;
	readonly plan?: LicensePlan;
	readonly features?: readonly string[];
	readonly expiresAt?: string;
}

/**
 * Stored license data
 */
export interface IStoredLicense {
	readonly licenseKey: string;
	readonly token?: string;
	readonly plan: LicensePlan;
	readonly features: readonly string[];
	readonly expiresAt: string | null;
	readonly lastValidated: string;
	readonly user?: {
		readonly email: string;
		readonly name: string;
	};
}

/**
 * License service interface
 */
export interface ILicenseService extends IDisposable {
	readonly _serviceBrand: undefined;

	/**
	 * Event fired when license status changes
	 */
	readonly onDidChangeLicenseStatus: Event<ILicenseStatus>;

	/**
	 * Get current license status
	 */
	getLicenseStatus(): Promise<ILicenseStatus>;

	/**
	 * Activate a license with the given key
	 */
	activateLicense(licenseKey: string): Promise<IActivationResult>;

	/**
	 * Deactivate current license
	 */
	deactivateLicense(): Promise<boolean>;

	/**
	 * Check if a specific feature is available
	 */
	hasFeature(feature: LicenseFeature): boolean;

	/**
	 * Get the current license plan
	 */
	getCurrentPlan(): LicensePlan;

	/**
	 * Get all available features for current plan
	 */
	getAvailableFeatures(): readonly string[];

	/**
	 * Check if license is valid
	 */
	isLicenseValid(): boolean;

	/**
	 * Get required plan for a feature
	 */
	getRequiredPlanForFeature(feature: LicenseFeature): LicensePlan;

	/**
	 * Refresh license status from server
	 */
	refreshLicenseStatus(): Promise<ILicenseStatus>;

	/**
	 * Remove a device from the license
	 */
	removeDevice(machineId: string): Promise<boolean>;

	/**
	 * Get stored license info
	 */
	getStoredLicense(): IStoredLicense | undefined;
}

export const ILicenseService = createDecorator<ILicenseService>('licenseService');

/**
 * Feature to plan mapping
 */
export const FEATURE_PLAN_MAP: Record<LicenseFeature, LicensePlan> = {
	// Free features
	[LicenseFeature.BasicEditor]: LicensePlan.Free,
	[LicenseFeature.SyntaxHighlighting]: LicensePlan.Free,
	[LicenseFeature.FileExplorer]: LicensePlan.Free,
	[LicenseFeature.Terminal]: LicensePlan.Free,
	[LicenseFeature.GitBasic]: LicensePlan.Free,

	// Pro features
	[LicenseFeature.AIAssistant]: LicensePlan.Pro,
	[LicenseFeature.AdvancedDebugging]: LicensePlan.Pro,
	[LicenseFeature.Templates]: LicensePlan.Pro,
	[LicenseFeature.CloudSync]: LicensePlan.Pro,
	[LicenseFeature.ExtensionsUnlimited]: LicensePlan.Pro,
	[LicenseFeature.HassanPanel]: LicensePlan.Pro,
	[LicenseFeature.CodeSnippets]: LicensePlan.Pro,
	[LicenseFeature.MultiCursor]: LicensePlan.Pro,

	// Team features
	[LicenseFeature.TeamCollaboration]: LicensePlan.Team,
	[LicenseFeature.SharedWorkspaces]: LicensePlan.Team,
	[LicenseFeature.TeamAnalytics]: LicensePlan.Team,
	[LicenseFeature.AdminDashboard]: LicensePlan.Team,
	[LicenseFeature.SSOIntegration]: LicensePlan.Team,
	[LicenseFeature.PrioritySupport]: LicensePlan.Team,
	[LicenseFeature.CodeReview]: LicensePlan.Team,
};

/**
 * Plan hierarchy for comparison
 */
export const PLAN_HIERARCHY: Record<LicensePlan, number> = {
	[LicensePlan.Free]: 0,
	[LicensePlan.Pro]: 1,
	[LicensePlan.Team]: 2
};

/**
 * Check if a plan includes another plan's features
 */
export function planIncludesFeatures(currentPlan: LicensePlan, requiredPlan: LicensePlan): boolean {
	return PLAN_HIERARCHY[currentPlan] >= PLAN_HIERARCHY[requiredPlan];
}

/**
 * License API configuration
 */
export interface ILicenseApiConfig {
	readonly apiUrl: string;
	readonly validateEndpoint: string;
	readonly activateEndpoint: string;
	readonly removeDeviceEndpoint: string;
}

export const DEFAULT_LICENSE_API_CONFIG: ILicenseApiConfig = {
	apiUrl: 'https://hassanide.com/api',
	validateEndpoint: '/license-v2.php',
	activateEndpoint: '/license-v2.php',
	removeDeviceEndpoint: '/license-v2.php'
};
