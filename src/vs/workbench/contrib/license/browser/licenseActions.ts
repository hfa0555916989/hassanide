/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { localize, localize2 } from '../../../../nls.js';
import { ILicenseService, LicensePlan, ILicenseDevice } from '../../../../platform/license/common/license.js';
import { Action2, registerAction2 } from '../../../../platform/actions/common/actions.js';
import { ServicesAccessor } from '../../../../platform/instantiation/common/instantiation.js';
import { IQuickInputService, IQuickPickItem } from '../../../../platform/quickinput/common/quickInput.js';
import { INotificationService, Severity } from '../../../../platform/notification/common/notification.js';
import { IDialogService } from '../../../../platform/dialogs/common/dialogs.js';
import { IOpenerService } from '../../../../platform/opener/common/opener.js';
import { URI } from '../../../../base/common/uri.js';
import { KeybindingWeight } from '../../../../platform/keybinding/common/keybindingsRegistry.js';
import { KeyCode, KeyMod } from '../../../../base/common/keyCodes.js';
import { Categories } from '../../../../platform/action/common/actionCommonCategories.js';

const HASSANIDE_PRICING_URL = 'https://hassanide.com/pricing';
const HASSANIDE_LICENSE_DASHBOARD_URL = 'https://hassanide.com/licenses';

/**
 * Activate a license key
 */
class ActivateLicenseAction extends Action2 {
	static readonly ID = 'hassanide.activateLicense';

	constructor() {
		super({
			id: ActivateLicenseAction.ID,
			title: localize2('activateLicense', "Activate License"),
			category: Categories.Preferences,
			f1: true,
			keybinding: {
				weight: KeybindingWeight.WorkbenchContrib,
				primary: KeyMod.CtrlCmd | KeyMod.Shift | KeyCode.KeyL
			}
		});
	}

	override async run(accessor: ServicesAccessor): Promise<void> {
		const quickInputService = accessor.get(IQuickInputService);
		const licenseService = accessor.get(ILicenseService);
		const notificationService = accessor.get(INotificationService);

		const licenseKey = await quickInputService.input({
			title: localize('enterLicenseKey', "Enter License Key"),
			prompt: localize('licenseKeyPrompt', "Enter your HassanIDE license key (format: PLAN-XXXX-XXXX-XXXX-XXXX)"),
			placeHolder: 'PRO-XXXX-XXXX-XXXX-XXXX',
			validateInput: async (value) => {
				const pattern = /^(FREE|PRO|TEAM)-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/;
				if (!pattern.test(value.toUpperCase())) {
					return localize('invalidKeyFormat', "Invalid license key format. Expected: PLAN-XXXX-XXXX-XXXX-XXXX");
				}
				return undefined;
			}
		});

		if (!licenseKey) {
			return;
		}

		const result = await licenseService.activateLicense(licenseKey);

		if (result.success) {
			notificationService.notify({
				severity: Severity.Info,
				message: localize('licenseActivated', "License activated successfully! Plan: {0}", result.plan || 'Pro')
			});
		} else {
			notificationService.notify({
				severity: Severity.Error,
				message: result.message || localize('activationFailed', "Failed to activate license")
			});
		}
	}
}

/**
 * Show current license status
 */
class ShowLicenseStatusAction extends Action2 {
	static readonly ID = 'hassanide.showLicenseStatus';

	constructor() {
		super({
			id: ShowLicenseStatusAction.ID,
			title: localize2('showLicenseStatus', "Show License Status"),
			category: Categories.Preferences,
			f1: true
		});
	}

	override async run(accessor: ServicesAccessor): Promise<void> {
		const licenseService = accessor.get(ILicenseService);
		const notificationService = accessor.get(INotificationService);

		const status = await licenseService.getLicenseStatus();

		const planNames: Record<LicensePlan, string> = {
			[LicensePlan.Free]: 'Starter (Free)',
			[LicensePlan.Pro]: 'Pro',
			[LicensePlan.Team]: 'Teams'
		};

		let message = localize(
			'licenseStatus',
			"Plan: {0}\nValid: {1}\nDevices: {2}/{3}",
			planNames[status.plan],
			status.isValid ? localize('yes', "Yes") : localize('no', "No"),
			status.activeDevices,
			status.maxDevices
		);

		if (status.daysRemaining !== null) {
			message += '\n' + localize('daysRemaining', "Days remaining: {0}", status.daysRemaining);
		}

		notificationService.notify({
			severity: status.isValid ? Severity.Info : Severity.Warning,
			message
		});
	}
}

/**
 * Deactivate current license
 */
class DeactivateLicenseAction extends Action2 {
	static readonly ID = 'hassanide.deactivateLicense';

	constructor() {
		super({
			id: DeactivateLicenseAction.ID,
			title: localize2('deactivateLicense', "Deactivate License"),
			category: Categories.Preferences,
			f1: true
		});
	}

	override async run(accessor: ServicesAccessor): Promise<void> {
		const licenseService = accessor.get(ILicenseService);
		const dialogService = accessor.get(IDialogService);
		const notificationService = accessor.get(INotificationService);

		const currentPlan = licenseService.getCurrentPlan();
		if (currentPlan === LicensePlan.Free) {
			notificationService.notify({
				severity: Severity.Info,
				message: localize('noActiveLicense', "No active license to deactivate.")
			});
			return;
		}

		const { confirmed } = await dialogService.confirm({
			type: 'warning',
			message: localize('confirmDeactivate', "Deactivate License"),
			detail: localize('confirmDeactivateDetail', "Are you sure you want to deactivate your license on this device? You can reactivate it later."),
			primaryButton: localize('deactivate', "Deactivate")
		});

		if (confirmed) {
			const success = await licenseService.deactivateLicense();
			if (success) {
				notificationService.notify({
					severity: Severity.Info,
					message: localize('licenseDeactivated', "License deactivated successfully.")
				});
			} else {
				notificationService.notify({
					severity: Severity.Error,
					message: localize('deactivationFailed', "Failed to deactivate license.")
				});
			}
		}
	}
}

/**
 * Upgrade to a paid plan
 */
class UpgradePlanAction extends Action2 {
	static readonly ID = 'hassanide.upgradePlan';

	constructor() {
		super({
			id: UpgradePlanAction.ID,
			title: localize2('upgradePlan', "Upgrade Plan"),
			category: Categories.Preferences,
			f1: true
		});
	}

	override async run(accessor: ServicesAccessor): Promise<void> {
		const openerService = accessor.get(IOpenerService);

		await openerService.open(URI.parse(HASSANIDE_PRICING_URL));
	}
}

/**
 * Manage devices linked to the license
 */
class ManageDevicesAction extends Action2 {
	static readonly ID = 'hassanide.manageDevices';

	constructor() {
		super({
			id: ManageDevicesAction.ID,
			title: localize2('manageDevices', "Manage Devices"),
			category: Categories.Preferences,
			f1: true
		});
	}

	override async run(accessor: ServicesAccessor): Promise<void> {
		const licenseService = accessor.get(ILicenseService);
		const quickInputService = accessor.get(IQuickInputService);
		const notificationService = accessor.get(INotificationService);
		const openerService = accessor.get(IOpenerService);

		const status = await licenseService.getLicenseStatus();

		if (!status || status.plan === LicensePlan.Free) {
			notificationService.notify({
				severity: Severity.Info,
				message: localize('noDevicesToManage', "No active license with multiple devices to manage.")
			});
			return;
		}

		interface DeviceQuickPickItem extends IQuickPickItem {
			id: string;
			device: ILicenseDevice;
		}

		const items: DeviceQuickPickItem[] = status.devices.map((device: ILicenseDevice) => ({
			id: device.machineId,
			label: device.machineName,
			description: localize('lastSeen', "Last seen: {0}", device.lastSeen),
			device
		}));

		items.push({
			id: 'manage-online',
			label: localize('manageOnline', "$(link-external) Manage online..."),
			description: localize('openDashboard', "Open license dashboard"),
			device: { machineId: '', machineName: '', addedAt: '', lastSeen: '' }
		});

		const selected = await quickInputService.pick(items, {
			title: localize('selectDevice', "Select a device to manage"),
			placeHolder: localize('selectDevicePlaceholder', "Select a device to remove or manage online")
		});

		if (!selected) {
			return;
		}

		if (selected.id === 'manage-online') {
			await openerService.open(URI.parse(HASSANIDE_LICENSE_DASHBOARD_URL));
			return;
		}

		const success = await licenseService.removeDevice(selected.id);
		if (success) {
			notificationService.notify({
				severity: Severity.Info,
				message: localize('deviceRemoved', "Device \"{0}\" removed successfully.", selected.label)
			});
		} else {
			notificationService.notify({
				severity: Severity.Error,
				message: localize('deviceRemovalFailed', "Failed to remove device.")
			});
		}
	}
}

/**
 * Register all license-related actions
 */
export function registerLicenseActions(): void {
	registerAction2(ActivateLicenseAction);
	registerAction2(ShowLicenseStatusAction);
	registerAction2(DeactivateLicenseAction);
	registerAction2(UpgradePlanAction);
	registerAction2(ManageDevicesAction);
}
