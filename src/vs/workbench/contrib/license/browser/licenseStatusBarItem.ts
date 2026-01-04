/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { Disposable, MutableDisposable } from '../../../../base/common/lifecycle.js';
import { IStatusbarService, IStatusbarEntry, StatusbarAlignment, IStatusbarEntryAccessor } from '../../../services/statusbar/browser/statusbar.js';
import { ILicenseService, LicensePlan } from '../../../../platform/license/common/license.js';
import { localize } from '../../../../nls.js';
import { ThemeColor, themeColorFromId } from '../../../../base/common/themables.js';

const STATUS_BAR_PRIORITY = 100;

export class LicenseStatusBarItem extends Disposable {

	private readonly _entry = this._register(new MutableDisposable<IStatusbarEntryAccessor>());

	constructor(
		@IStatusbarService private readonly statusbarService: IStatusbarService,
		@ILicenseService private readonly licenseService: ILicenseService
	) {
		super();

		this._updateStatusBar();

		this._register(this.licenseService.onDidChangeLicenseStatus(() => {
			this._updateStatusBar();
		}));
	}

	private _updateStatusBar(): void {
		const plan = this.licenseService.getCurrentPlan();
		const isValid = this.licenseService.isLicenseValid();

		let text: string;
		let tooltip: string;
		let backgroundColor: ThemeColor | undefined;
		let command: string;

		switch (plan) {
			case LicensePlan.Team:
				text = '$(organization) Teams';
				tooltip = localize('teamsLicense', "HassanIDE Teams License - Click to manage");
				backgroundColor = themeColorFromId('statusBarItem.prominentBackground');
				command = 'hassanide.showLicenseStatus';
				break;

			case LicensePlan.Pro:
				text = '$(star-full) Pro';
				tooltip = localize('proLicense', "HassanIDE Pro License - Click to manage");
				backgroundColor = themeColorFromId('statusBarItem.prominentBackground');
				command = 'hassanide.showLicenseStatus';
				break;

			case LicensePlan.Free:
			default:
				text = '$(lock) Free';
				tooltip = localize('freeLicense', "HassanIDE Free Plan - Click to upgrade");
				command = 'hassanide.upgradePlan';
				break;
		}

		// If license is not valid (expired or offline too long), show warning
		if (plan !== LicensePlan.Free && !isValid) {
			text = '$(warning) License Issue';
			tooltip = localize('licenseIssue', "License validation failed - Click to resolve");
			backgroundColor = themeColorFromId('statusBarItem.warningBackground');
			command = 'hassanide.showLicenseStatus';
		}

		const entry: IStatusbarEntry = {
			name: localize('licenseName', "HassanIDE License"),
			text,
			tooltip,
			ariaLabel: tooltip,
			backgroundColor,
			command,
			showInAllWindows: true
		};

		if (!this._entry.value) {
			this._entry.value = this.statusbarService.addEntry(
				entry,
				'hassanide.license',
				StatusbarAlignment.RIGHT,
				STATUS_BAR_PRIORITY
			);
		} else {
			this._entry.value.update(entry);
		}
	}
}
