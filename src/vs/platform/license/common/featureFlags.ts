/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { ILicenseService, LicenseFeature, LicensePlan, FEATURE_PLAN_MAP, planIncludesFeatures } from './license.js';
import { IDialogService } from '../../dialogs/common/dialogs.js';
import { IOpenerService } from '../../opener/common/opener.js';
import { URI } from '../../../base/common/uri.js';
import { localize } from '../../../nls.js';

const HASSANIDE_PRICING_URL = 'https://hassanide.com/pricing';

/**
 * Feature flag service for checking feature availability
 */
export class FeatureFlags {

	constructor(
		private readonly licenseService: ILicenseService,
		private readonly dialogService: IDialogService,
		private readonly openerService: IOpenerService
	) { }

	/**
	 * Check if a feature is available for the current license
	 */
	isFeatureAvailable(feature: LicenseFeature): boolean {
		return this.licenseService.hasFeature(feature);
	}

	/**
	 * Get the minimum required plan for a feature
	 */
	getRequiredPlan(feature: LicenseFeature): LicensePlan {
		return FEATURE_PLAN_MAP[feature];
	}

	/**
	 * Check if current plan includes the features of another plan
	 */
	hasPlanFeatures(requiredPlan: LicensePlan): boolean {
		const currentPlan = this.licenseService.getCurrentPlan();
		return planIncludesFeatures(currentPlan, requiredPlan);
	}

	/**
	 * Guard a feature - returns true if available, shows upgrade dialog if not
	 */
	async guardFeature(feature: LicenseFeature): Promise<boolean> {
		if (this.isFeatureAvailable(feature)) {
			return true;
		}

		await this.showUpgradeDialog(feature);
		return false;
	}

	/**
	 * Show upgrade dialog for a specific feature
	 */
	async showUpgradeDialog(feature: LicenseFeature): Promise<void> {
		const requiredPlan = this.getRequiredPlan(feature);
		const featureName = this._getFeatureDisplayName(feature);
		const planName = this._getPlanDisplayName(requiredPlan);

		const { result } = await this.dialogService.prompt({
			type: 'info',
			message: localize('upgradeRequired', "Upgrade Required"),
			detail: localize(
				'upgradeRequiredDetail',
				"The feature \"{0}\" requires the {1} plan.\n\nUpgrade now to unlock this and many more premium features!",
				featureName,
				planName
			),
			buttons: [
				{
					label: localize('upgradeNow', "Upgrade Now"),
					run: () => true
				}
			],
			cancelButton: {
				label: localize('later', "Maybe Later"),
				run: () => false
			}
		});

		if (result) {
			await this.openerService.open(URI.parse(`${HASSANIDE_PRICING_URL}?feature=${feature}`));
		}
	}

	/**
	 * Execute a function only if feature is available
	 */
	async withFeature<T>(
		feature: LicenseFeature,
		fn: () => T | Promise<T>,
		fallback?: T
	): Promise<T | undefined> {
		if (await this.guardFeature(feature)) {
			return fn();
		}
		return fallback;
	}

	/**
	 * Create a decorator for feature-gated methods
	 */
	static requireFeature(feature: LicenseFeature) {
		return function (
			_target: unknown,
			_propertyKey: string,
			descriptor: PropertyDescriptor
		) {
			const originalMethod = descriptor.value;

			descriptor.value = async function (this: { _featureFlags?: FeatureFlags }, ...args: unknown[]) {
				if (this._featureFlags && !(await this._featureFlags.guardFeature(feature))) {
					return undefined;
				}
				return originalMethod.apply(this, args);
			};

			return descriptor;
		};
	}

	private _getFeatureDisplayName(feature: LicenseFeature): string {
		const names: Record<LicenseFeature, string> = {
			[LicenseFeature.BasicEditor]: localize('feature.basicEditor', "Basic Editor"),
			[LicenseFeature.SyntaxHighlighting]: localize('feature.syntaxHighlighting', "Syntax Highlighting"),
			[LicenseFeature.FileExplorer]: localize('feature.fileExplorer', "File Explorer"),
			[LicenseFeature.Terminal]: localize('feature.terminal', "Terminal"),
			[LicenseFeature.GitBasic]: localize('feature.gitBasic', "Git Integration"),
			[LicenseFeature.AIAssistant]: localize('feature.aiAssistant', "AI Assistant"),
			[LicenseFeature.AdvancedDebugging]: localize('feature.advancedDebugging', "Advanced Debugging"),
			[LicenseFeature.Templates]: localize('feature.templates', "Project Templates"),
			[LicenseFeature.CloudSync]: localize('feature.cloudSync', "Cloud Sync"),
			[LicenseFeature.ExtensionsUnlimited]: localize('feature.extensionsUnlimited', "Unlimited Extensions"),
			[LicenseFeature.HassanPanel]: localize('feature.hassanPanel', "Hassan Panel"),
			[LicenseFeature.CodeSnippets]: localize('feature.codeSnippets', "Code Snippets Library"),
			[LicenseFeature.MultiCursor]: localize('feature.multiCursor', "Multi Cursor Editing"),
			[LicenseFeature.TeamCollaboration]: localize('feature.teamCollaboration', "Team Collaboration"),
			[LicenseFeature.SharedWorkspaces]: localize('feature.sharedWorkspaces', "Shared Workspaces"),
			[LicenseFeature.TeamAnalytics]: localize('feature.teamAnalytics', "Team Analytics"),
			[LicenseFeature.AdminDashboard]: localize('feature.adminDashboard', "Admin Dashboard"),
			[LicenseFeature.SSOIntegration]: localize('feature.ssoIntegration', "SSO Integration"),
			[LicenseFeature.PrioritySupport]: localize('feature.prioritySupport', "Priority Support"),
			[LicenseFeature.CodeReview]: localize('feature.codeReview', "Code Review Tools")
		};
		return names[feature] || feature;
	}

	private _getPlanDisplayName(plan: LicensePlan): string {
		const names: Record<LicensePlan, string> = {
			[LicensePlan.Free]: 'Starter',
			[LicensePlan.Pro]: 'Pro',
			[LicensePlan.Team]: 'Teams'
		};
		return names[plan] || plan;
	}
}

/**
 * Helper function to check feature availability without dialog
 */
export function checkFeatureAvailable(
	licenseService: ILicenseService,
	feature: LicenseFeature
): boolean {
	return licenseService.hasFeature(feature);
}

/**
 * Helper to get all features for a plan
 */
export function getFeaturesForPlan(plan: LicensePlan): LicenseFeature[] {
	return Object.entries(FEATURE_PLAN_MAP)
		.filter(([_, requiredPlan]) => planIncludesFeatures(plan, requiredPlan))
		.map(([feature]) => feature as LicenseFeature);
}

/**
 * Helper to get features that require upgrade
 */
export function getLockedFeatures(currentPlan: LicensePlan): LicenseFeature[] {
	return Object.entries(FEATURE_PLAN_MAP)
		.filter(([_, requiredPlan]) => !planIncludesFeatures(currentPlan, requiredPlan))
		.map(([feature]) => feature as LicenseFeature);
}
