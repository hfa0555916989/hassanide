#!/usr/bin/env node
/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Hassan Tech. All rights reserved.
 *  Licensed under the Proprietary License. See LICENSE-HASSANIDE.txt for license information.
 *--------------------------------------------------------------------------------------------*/
/*---------------------------------------------------------------------------------------------
 *  Hassan IDE Build Script
 *  Builds Hassan IDE for Windows, macOS, and Linux
 *--------------------------------------------------------------------------------------------*/

const { execSync, spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

const ROOT = path.dirname(__dirname);
const PRODUCT_JSON = path.join(ROOT, 'product.json');

// Colors for console output
const colors = {
	reset: '\x1b[0m',
	bright: '\x1b[1m',
	green: '\x1b[32m',
	blue: '\x1b[34m',
	yellow: '\x1b[33m',
	red: '\x1b[31m',
	cyan: '\x1b[36m'
};

function log(message, color = colors.reset) {
	console.log(`${color}${message}${colors.reset}`);
}

function logStep(step, message) {
	log(`\n[${step}] ${message}`, colors.cyan);
}

function logSuccess(message) {
	log(`✓ ${message}`, colors.green);
}

function logError(message) {
	log(`✗ ${message}`, colors.red);
}

function logInfo(message) {
	log(`ℹ ${message}`, colors.blue);
}

/**
 * Verify product.json is configured for Hassan IDE
 */
function verifyProductConfig() {
	logStep(1, 'Verifying product configuration...');

	const product = JSON.parse(fs.readFileSync(PRODUCT_JSON, 'utf8'));

	if (product.nameShort !== 'Hassan IDE') {
		throw new Error('product.json is not configured for Hassan IDE');
	}

	logSuccess(`Product: ${product.nameLong}`);
	logInfo(`Application Name: ${product.applicationName}`);
	logInfo(`Win32 Dir: ${product.win32DirName}`);
	logInfo(`Darwin Bundle: ${product.darwinBundleIdentifier}`);
	logInfo(`Linux Icon: ${product.linuxIconName}`);
}

/**
 * Verify icons are in place
 */
function verifyIcons() {
	logStep(2, 'Verifying icons...');

	const iconDir = path.join(ROOT, 'resources', 'hassanide');

	if (!fs.existsSync(iconDir)) {
		throw new Error('Hassan IDE icons directory not found');
	}

	const requiredFiles = ['logo.svg', 'icon-square.svg', 'favicon.svg'];
	for (const file of requiredFiles) {
		const filePath = path.join(iconDir, file);
		if (!fs.existsSync(filePath)) {
			throw new Error(`Missing icon file: ${file}`);
		}
		logSuccess(`Found: ${file}`);
	}
}

/**
 * Install dependencies
 */
function installDependencies() {
	logStep(3, 'Installing dependencies...');

	try {
		execSync('npm install', { cwd: ROOT, stdio: 'inherit' });
		logSuccess('Dependencies installed');
	} catch (error) {
		throw new Error('Failed to install dependencies');
	}
}

/**
 * Compile TypeScript
 */
function compileTypeScript() {
	logStep(4, 'Compiling TypeScript...');

	try {
		execSync('npm run compile', { cwd: ROOT, stdio: 'inherit' });
		logSuccess('TypeScript compiled');
	} catch (error) {
		throw new Error('Failed to compile TypeScript');
	}
}

/**
 * Build for a specific platform
 */
function buildPlatform(platform, arch = 'x64') {
	logStep(5, `Building for ${platform}-${arch}...`);

	const gulpCmd = process.platform === 'win32' ? 'npx.cmd' : 'npx';

	try {
		execSync(`${gulpCmd} gulp vscode-${platform}-${arch}-min`, {
			cwd: ROOT,
			stdio: 'inherit'
		});
		logSuccess(`Built for ${platform}-${arch}`);
	} catch (error) {
		logError(`Failed to build for ${platform}-${arch}: ${error.message}`);
		return false;
	}

	return true;
}

/**
 * Create installer for Windows
 */
function createWindowsInstaller(arch = 'x64') {
	logStep(6, `Creating Windows installer (${arch})...`);

	try {
		execSync(`npx gulp vscode-win32-${arch}-inno-setup`, {
			cwd: ROOT,
			stdio: 'inherit'
		});
		logSuccess(`Windows installer created for ${arch}`);
	} catch (error) {
		logError(`Failed to create Windows installer: ${error.message}`);
	}
}

/**
 * Create DMG for macOS
 */
function createMacDMG(arch = 'x64') {
	logStep(7, `Creating macOS DMG (${arch})...`);

	try {
		execSync(`npx gulp vscode-darwin-${arch}-min-archive`, {
			cwd: ROOT,
			stdio: 'inherit'
		});
		logSuccess(`macOS DMG created for ${arch}`);
	} catch (error) {
		logError(`Failed to create macOS DMG: ${error.message}`);
	}
}

/**
 * Create Linux packages
 */
function createLinuxPackages(arch = 'x64') {
	logStep(8, `Creating Linux packages (${arch})...`);

	try {
		// Create .deb
		execSync(`npx gulp vscode-linux-${arch}-build-deb`, {
			cwd: ROOT,
			stdio: 'inherit'
		});
		logSuccess(`Linux .deb created for ${arch}`);

		// Create .rpm
		execSync(`npx gulp vscode-linux-${arch}-build-rpm`, {
			cwd: ROOT,
			stdio: 'inherit'
		});
		logSuccess(`Linux .rpm created for ${arch}`);
	} catch (error) {
		logError(`Failed to create Linux packages: ${error.message}`);
	}
}

/**
 * Main build function
 */
async function main() {
	log('\n╔══════════════════════════════════════════════╗', colors.cyan);
	log('║        Hassan IDE Build Script               ║', colors.cyan);
	log('║   محرر الأكواد العربي الاحترافي              ║', colors.cyan);
	log('╚══════════════════════════════════════════════╝\n', colors.cyan);

	const args = process.argv.slice(2);
	const platforms = args.length > 0 ? args : ['all'];

	try {
		// Verification steps
		verifyProductConfig();
		verifyIcons();

		// Ask about build type
		const buildAll = platforms.includes('all');
		const buildWindows = buildAll || platforms.includes('win32') || platforms.includes('windows');
		const buildMac = buildAll || platforms.includes('darwin') || platforms.includes('mac');
		const buildLinux = buildAll || platforms.includes('linux');

		// Install and compile
		if (!platforms.includes('--skip-compile')) {
			installDependencies();
			compileTypeScript();
		}

		// Build platforms
		const crossBuild = platforms.includes('--cross') || platforms.includes('-x');

		if (buildWindows) {
			if (buildPlatform('win32', 'x64')) {
				if (process.platform === 'win32') {
					createWindowsInstaller('x64');
				} else {
					logInfo('Skipping Windows installer (requires Windows)');
				}
			}
		}

		if (buildMac) {
			if (process.platform === 'darwin' || crossBuild) {
				if (buildPlatform('darwin', 'x64')) {
					if (process.platform === 'darwin') {
						createMacDMG('x64');
					} else {
						logInfo('Skipping macOS DMG (requires macOS for signing/packaging)');
					}
				}
				if (buildPlatform('darwin', 'arm64')) {
					if (process.platform === 'darwin') {
						createMacDMG('arm64');
					} else {
						logInfo('Skipping macOS ARM64 DMG (requires macOS for signing/packaging)');
					}
				}
			} else {
				logInfo('Use --cross or -x flag to build macOS from Windows/Linux');
			}
		}

		if (buildLinux) {
			if (process.platform === 'linux' || crossBuild) {
				if (buildPlatform('linux', 'x64')) {
					if (process.platform === 'linux') {
						createLinuxPackages('x64');
					} else {
						logInfo('Skipping Linux packages (requires Linux for .deb/.rpm)');
					}
				}
			} else {
				logInfo('Use --cross or -x flag to build Linux from Windows/macOS');
			}
		}

		log('\n╔══════════════════════════════════════════════╗', colors.green);
		log('║           Build Complete!                    ║', colors.green);
		log('╚══════════════════════════════════════════════╝\n', colors.green);

		logInfo('Output files are in the parent directory (../)');
		logInfo('Windows: ../VSCode-win32-x64/ and .build/win32-x64/');
		logInfo('macOS: ../VSCode-darwin-x64/ and ../VSCode-darwin-arm64/');
		logInfo('Linux: ../VSCode-linux-x64/ and .build/linux/');

	} catch (error) {
		logError(`Build failed: ${error.message}`);
		process.exit(1);
	}
}

main();
