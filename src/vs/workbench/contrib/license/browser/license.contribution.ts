/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { Disposable } from '../../../../base/common/lifecycle.js';
import { IWorkbenchContribution, registerWorkbenchContribution2, WorkbenchPhase } from '../../../common/contributions.js';
import { IInstantiationService } from '../../../../platform/instantiation/common/instantiation.js';
import { LicenseStatusBarItem } from './licenseStatusBarItem.js';
import { registerLicenseActions } from './licenseActions.js';

/**
 * License contribution that initializes the license system
 */
class LicenseContribution extends Disposable implements IWorkbenchContribution {

	static readonly ID = 'workbench.contrib.license';

	constructor(
		@IInstantiationService private readonly instantiationService: IInstantiationService
	) {
		super();

		// Register license actions
		registerLicenseActions();

		// Create status bar item
		this._register(this.instantiationService.createInstance(LicenseStatusBarItem));
	}
}

// Register the contribution
registerWorkbenchContribution2(
	LicenseContribution.ID,
	LicenseContribution,
	WorkbenchPhase.AfterRestored
);
