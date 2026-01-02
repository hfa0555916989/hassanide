/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { Action2, registerAction2 } from '../../../../platform/actions/common/actions.js';
import { INotificationService } from '../../../../platform/notification/common/notification.js';
import { IQuickInputService } from '../../../../platform/quickinput/common/quickInput.js';
import { InstantiationType, registerSingleton } from '../../../../platform/instantiation/common/extensions.js';
import { ServicesAccessor } from '../../../../platform/instantiation/common/instantiation.js';
import { localize } from '../../../../nls.js';
import { IStatusbarEntry, IStatusbarEntryAccessor, IStatusbarService, StatusbarAlignment } from '../../../services/statusbar/browser/statusbar.js';
import { WorkbenchPhase, registerWorkbenchContribution2 } from '../../../common/contributions.js';
import { Disposable } from '../../../../base/common/lifecycle.js';
import { ILicenseService } from '../common/licensing.js';
import { LicenseService } from './licenseService.js';

registerSingleton(ILicenseService, LicenseService, InstantiationType.Delayed);

class ActivateLicenseAction extends Action2 {
	constructor() {
		super({
			id: 'hassanide.license.activate',
			title: {
				value: localize('license.activate', "Activate License"),
				original: 'Activate License'
			},
			category: localize('license.category', "Hassan IDE"),
			f1: true
		});
	}

	async run(accessor: ServicesAccessor): Promise<void> {
		const quickInputService = accessor.get(IQuickInputService);
		const notificationService = accessor.get(INotificationService);
		const licenseService = accessor.get(ILicenseService);

		const token = await quickInputService.input({
			prompt: localize('license.activate.prompt', "Enter your license key"),
			placeHolder: localize('license.activate.placeholder', "License key"),
			password: true,
			ignoreFocusOut: true
		});

		if (!token) {
			return;
		}

		await licenseService.activate(token);
		notificationService.info(localize('license.activate.success', "License activated."));
	}
}

registerAction2(ActivateLicenseAction);

class LicenseStatusBarContribution extends Disposable {
	static readonly ID = 'workbench.contrib.licenseStatus';

	private entry: IStatusbarEntryAccessor | undefined;

	constructor(
		@IStatusbarService private readonly statusbarService: IStatusbarService,
		@ILicenseService private readonly licenseService: ILicenseService,
	) {
		super();

		this._register(this.licenseService.onDidChangeLicense(isActive => this.updateEntry(isActive)));
		this.licenseService.isActive().then(isActive => this.updateEntry(isActive));
	}

	private updateEntry(isActive: boolean): void {
		const text = isActive
			? localize('license.active', "$(verified) License Active")
			: localize('license.inactive', "Activate License");
		const tooltip = isActive
			? localize('license.active.tooltip', "Your license is active.")
			: localize('license.inactive.tooltip', "Activate your license to unlock paid features.");

		const entry: IStatusbarEntry = {
			name: localize('license.status.name', "License Status"),
			text,
			tooltip,
			command: 'hassanide.license.activate'
		};

		if (this.entry) {
			this.entry.update(entry);
		} else {
			this.entry = this.statusbarService.addEntry(entry, 'status.license', StatusbarAlignment.RIGHT, 99);
		}
	}
}

registerWorkbenchContribution2(LicenseStatusBarContribution.ID, LicenseStatusBarContribution, WorkbenchPhase.AfterRestored);
