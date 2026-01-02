/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { Event } from '../../../../base/common/event.js';
import { createDecorator } from '../../../../platform/instantiation/common/instantiation.js';

export interface ILicenseFeatureFlags {
	readonly paidFeaturesEnabled: boolean;
}

export interface ILicenseService {
	readonly _serviceBrand: undefined;
	readonly onDidChangeLicense: Event<boolean>;
	isActive(): Promise<boolean>;
	activate(token: string): Promise<void>;
	clear(): Promise<void>;
	getFeatureFlags(): Promise<ILicenseFeatureFlags>;
}

export const ILicenseService = createDecorator<ILicenseService>('licenseService');
