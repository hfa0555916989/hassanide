/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { localize } from '../../../../nls.js';
import { IDialogService } from '../../../../platform/dialogs/common/dialogs.js';
import { IOpenerService } from '../../../../platform/opener/common/opener.js';
import { URI } from '../../../../base/common/uri.js';
import { LicenseFeature, LicensePlan, FEATURE_PLAN_MAP } from '../../../../platform/license/common/license.js';

const HASSANIDE_PRICING_URL = 'https://hassanide.com/pricing';

/**
 * Feature display names for the upgrade dialog
 */
const FEATURE_DISPLAY_NAMES: Record<LicenseFeature, string> = {
	[LicenseFeature.BasicEditor]: localize('basicEditor', "Basic Editor"),
	[LicenseFeature.SyntaxHighlighting]: localize('syntaxHighlighting', "Syntax Highlighting"),
	[LicenseFeature.FileExplorer]: localize('fileExplorer', "File Explorer"),
	[LicenseFeature.Terminal]: localize('terminal', "Integrated Terminal"),
	[LicenseFeature.GitBasic]: localize('gitBasic', "Git Basic"),
	[LicenseFeature.AIAssistant]: localize('aiAssistant', "AI Assistant"),
	[LicenseFeature.AdvancedDebugging]: localize('advancedDebugging', "Advanced Debugging"),
	[LicenseFeature.Templates]: localize('templates', "Project Templates"),
	[LicenseFeature.CloudSync]: localize('cloudSync', "Cloud Sync"),
	[LicenseFeature.ExtensionsUnlimited]: localize('extensionsUnlimited', "Unlimited Extensions"),
	[LicenseFeature.HassanPanel]: localize('hassanPanel', "Hassan Panel"),
	[LicenseFeature.CodeSnippets]: localize('codeSnippets', "Code Snippets"),
	[LicenseFeature.MultiCursor]: localize('multiCursor', "Multi Cursor"),
	[LicenseFeature.TeamCollaboration]: localize('teamCollaboration', "Team Collaboration"),
	[LicenseFeature.SharedWorkspaces]: localize('sharedWorkspaces', "Shared Workspaces"),
	[LicenseFeature.TeamAnalytics]: localize('teamAnalytics', "Team Analytics"),
	[LicenseFeature.AdminDashboard]: localize('adminDashboard', "Admin Dashboard"),
	[LicenseFeature.SSOIntegration]: localize('ssoIntegration', "SSO Integration"),
	[LicenseFeature.PrioritySupport]: localize('prioritySupport', "Priority Support"),
	[LicenseFeature.CodeReview]: localize('codeReview', "Code Review")
};

/**
 * Plan display names
 */
const PLAN_DISPLAY_NAMES: Record<LicensePlan, string> = {
	[LicensePlan.Free]: 'Starter',
	[LicensePlan.Pro]: 'Pro',
	[LicensePlan.Team]: 'Teams'
};

/**
 * Plan prices
 */
const PLAN_PRICES: Record<LicensePlan, string> = {
	[LicensePlan.Free]: localize('free', "Free"),
	[LicensePlan.Pro]: '29 SAR/month',
	[LicensePlan.Team]: '99 SAR/month'
};

/**
 * Show upgrade dialog when a premium feature is accessed
 */
export async function showUpgradeDialog(
	feature: LicenseFeature,
	dialogService: IDialogService,
	openerService: IOpenerService
): Promise<boolean> {
	const requiredPlan = FEATURE_PLAN_MAP[feature];
	const featureName = FEATURE_DISPLAY_NAMES[feature];
	const planName = PLAN_DISPLAY_NAMES[requiredPlan];
	const planPrice = PLAN_PRICES[requiredPlan];

	const { result } = await dialogService.prompt({
		type: 'info',
		message: localize('upgradeRequired', "Upgrade Required"),
		detail: localize('upgradeDetail',
			"The feature \"{0}\" requires {1} plan ({2}).\n\nUpgrade now to unlock this feature and many more!",
			featureName,
			planName,
			planPrice
		),
		buttons: [
			{
				label: localize('upgradeNow', "Upgrade Now"),
				run: () => 'upgrade' as const
			},
			{
				label: localize('learnMore', "Learn More"),
				run: () => 'learn' as const
			}
		],
		cancelButton: {
			label: localize('cancel', "Cancel"),
			run: () => 'cancel' as const
		}
	});

	if (result === 'upgrade') {
		// Upgrade Now - open pricing page with plan preselected
		const url = `${HASSANIDE_PRICING_URL}?plan=${requiredPlan}`;
		await openerService.open(URI.parse(url));
		return true;
	} else if (result === 'learn') {
		// Learn More - open pricing page
		await openerService.open(URI.parse(HASSANIDE_PRICING_URL));
		return false;
	}

	return false;
}

/**
 * Show a quick notification for premium features
 */
export function getFeatureRequiredMessage(feature: LicenseFeature): string {
	const requiredPlan = FEATURE_PLAN_MAP[feature];
	const featureName = FEATURE_DISPLAY_NAMES[feature];
	const planName = PLAN_DISPLAY_NAMES[requiredPlan];

	return localize('featureRequiresPlan',
		"{0} requires {1} plan. Upgrade to unlock.",
		featureName,
		planName
	);
}

/**
 * Check if feature is available, show upgrade dialog if not
 */
export async function requireFeature(
	feature: LicenseFeature,
	hasFeature: boolean,
	dialogService: IDialogService,
	openerService: IOpenerService
): Promise<boolean> {
	if (hasFeature) {
		return true;
	}

	await showUpgradeDialog(feature, dialogService, openerService);
	return false;
}
