/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

import { isWindows } from '../../../../base/common/platform.js';
import { join, dirname } from '../../../../base/common/path.js';
import { URI } from '../../../../base/common/uri.js';
import { localize } from '../../../../nls.js';
import { IConfigurationService, ConfigurationTarget } from '../../../../platform/configuration/common/configuration.js';
import { IEnvironmentService } from '../../../../platform/environment/common/environment.js';
import { FileOperationResult, IFileService, toFileOperationResult } from '../../../../platform/files/common/files.js';
import { ILogService } from '../../../../platform/log/common/log.js';
import { IProgressService, ProgressLocation } from '../../../../platform/progress/common/progress.js';
import { IStorageService, StorageScope, StorageTarget } from '../../../../platform/storage/common/storage.js';
import { WorkbenchPhase, registerWorkbenchContribution2 } from '../../../common/contributions.js';
import { IExtensionsWorkbenchService } from '../../extensions/common/extensions.js';

const defaultExtensionsStorageKey = 'hassanide.defaultExtensionsInstalled';
const defaultSettingsStorageKey = 'hassanide.defaultSettingsApplied';
const defaultExtensionsFileName = 'default-extensions.json';

class FirstRunBootstrapContribution {
	static readonly ID = 'workbench.contrib.firstRunBootstrap';

	constructor(
		@IEnvironmentService private readonly environmentService: IEnvironmentService,
		@IExtensionsWorkbenchService private readonly extensionsWorkbenchService: IExtensionsWorkbenchService,
		@IStorageService private readonly storageService: IStorageService,
		@IFileService private readonly fileService: IFileService,
		@IProgressService private readonly progressService: IProgressService,
		@IConfigurationService private readonly configurationService: IConfigurationService,
		@ILogService private readonly logService: ILogService,
	) {
		this.initialize().catch(error => this.logService.error('Failed to run first-run bootstrap', error));
	}

	private async initialize(): Promise<void> {
		await this.installDefaultExtensions();
		await this.applyDefaultSettings();
	}

	private async installDefaultExtensions(): Promise<void> {
		if (this.storageService.getBoolean(defaultExtensionsStorageKey, StorageScope.PROFILE, false)) {
			return;
		}

		const defaultExtensions = await this.readDefaultExtensions();
		if (!defaultExtensions.length) {
			this.storageService.store(defaultExtensionsStorageKey, true, StorageScope.PROFILE, StorageTarget.USER);
			return;
		}

		const installedExtensions = new Set(this.extensionsWorkbenchService.installed.map(extension => extension.identifier.id.toLowerCase()));
		const extensionsToInstall = defaultExtensions.filter(id => !installedExtensions.has(id.toLowerCase()));
		if (!extensionsToInstall.length) {
			this.storageService.store(defaultExtensionsStorageKey, true, StorageScope.PROFILE, StorageTarget.USER);
			return;
		}

		await this.progressService.withProgress({
			location: ProgressLocation.Notification,
			title: localize('defaultExtensions.installTitle', "Installing Default Extensions")
		}, async progress => {
			for (const extensionId of extensionsToInstall) {
				progress.report({ message: localize('defaultExtensions.installing', "Installing {0}", extensionId) });
				try {
					await this.extensionsWorkbenchService.install(extensionId);
				} catch (error) {
					this.logService.error(`Failed to install default extension: ${extensionId}`, error);
				}
			}
		});

		this.storageService.store(defaultExtensionsStorageKey, true, StorageScope.PROFILE, StorageTarget.USER);
	}

	private async readDefaultExtensions(): Promise<string[]> {
		const locations = [
			URI.file(join(this.environmentService.appRoot, 'resources', defaultExtensionsFileName)),
			URI.file(join(dirname(this.environmentService.appRoot), defaultExtensionsFileName))
		];

		for (const location of locations) {
			try {
				const contents = await this.fileService.readFile(location);
				const parsed = JSON.parse(contents.value.toString());
				if (Array.isArray(parsed)) {
					return parsed;
				}
				if (parsed && Array.isArray(parsed.extensions)) {
					return parsed.extensions;
				}
			} catch (error) {
				if (toFileOperationResult(error) !== FileOperationResult.FILE_NOT_FOUND) {
					this.logService.error('Failed to read default extensions', error);
				}
			}
		}

		return [];
	}

	private async applyDefaultSettings(): Promise<void> {
		if (this.storageService.getBoolean(defaultSettingsStorageKey, StorageScope.PROFILE, false)) {
			return;
		}

		const settings: Array<{ key: string; value: unknown }> = [
			{ key: 'editor.formatOnSave', value: true },
			{ key: 'editor.defaultFormatter', value: 'esbenp.prettier-vscode' },
			{ key: 'eslint.validate', value: ['javascript', 'javascriptreact', 'typescript', 'typescriptreact'] }
		];

		if (isWindows) {
			settings.push({ key: 'terminal.integrated.defaultProfile.windows', value: 'PowerShell' });
		}

		for (const setting of settings) {
			const inspection = this.configurationService.inspect(setting.key);
			const hasUserValue = inspection.userValue !== undefined || inspection.userLocalValue !== undefined || inspection.userRemoteValue !== undefined;
			if (!hasUserValue) {
				await this.configurationService.updateValue(setting.key, setting.value, ConfigurationTarget.USER);
			}
		}

		this.storageService.store(defaultSettingsStorageKey, true, StorageScope.PROFILE, StorageTarget.USER);
	}
}

registerWorkbenchContribution2(FirstRunBootstrapContribution.ID, FirstRunBootstrapContribution, WorkbenchPhase.AfterRestored);
