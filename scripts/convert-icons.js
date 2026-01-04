#!/usr/bin/env node
/*---------------------------------------------------------------------------------------------
 *  Hassan IDE - Icon Converter Script
 *  Converts SVG icons to platform-specific formats (ICO, ICNS, PNG)
 *  Note: Requires ImageMagick or similar tool installed on the system
 *--------------------------------------------------------------------------------------------*/

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const ROOT = path.dirname(__dirname);
const HASSANIDE_ICONS = path.join(ROOT, 'resources', 'hassanide');
const WIN32_ICONS = path.join(ROOT, 'resources', 'win32');
const DARWIN_ICONS = path.join(ROOT, 'resources', 'darwin');
const LINUX_ICONS = path.join(ROOT, 'resources', 'linux', 'code');

// Icon sizes needed for different platforms
const ICON_SIZES = {
	ico: [16, 24, 32, 48, 64, 128, 256],
	icns: [16, 32, 64, 128, 256, 512, 1024],
	png: [16, 22, 24, 32, 48, 64, 128, 256, 512]
};

function log(message) {
	console.log(`[Hassan IDE Icons] ${message}`);
}

function checkImageMagick() {
	try {
		execSync('magick -version', { stdio: 'pipe' });
		return true;
	} catch {
		try {
			execSync('convert -version', { stdio: 'pipe' });
			return true;
		} catch {
			return false;
		}
	}
}

function getMagickCommand() {
	try {
		execSync('magick -version', { stdio: 'pipe' });
		return 'magick';
	} catch {
		return 'convert';
	}
}

function convertSVGtoPNG(svgPath, pngPath, size) {
	const magick = getMagickCommand();
	try {
		execSync(`${magick} -background none -resize ${size}x${size} "${svgPath}" "${pngPath}"`, { stdio: 'pipe' });
		return true;
	} catch (error) {
		console.error(`Failed to convert ${svgPath} to PNG size ${size}:`, error.message);
		return false;
	}
}

function createICO(svgPath, icoPath) {
	const magick = getMagickCommand();
	const tempDir = path.join(ROOT, 'temp-icons');

	if (!fs.existsSync(tempDir)) {
		fs.mkdirSync(tempDir, { recursive: true });
	}

	try {
		// Create PNGs for each size
		const pngFiles = [];
		for (const size of ICON_SIZES.ico) {
			const pngPath = path.join(tempDir, `icon-${size}.png`);
			if (convertSVGtoPNG(svgPath, pngPath, size)) {
				pngFiles.push(pngPath);
			}
		}

		// Combine into ICO
		const pngList = pngFiles.map(f => `"${f}"`).join(' ');
		execSync(`${magick} ${pngList} "${icoPath}"`, { stdio: 'pipe' });

		// Cleanup
		pngFiles.forEach(f => fs.unlinkSync(f));
		fs.rmdirSync(tempDir);

		return true;
	} catch (error) {
		console.error('Failed to create ICO:', error.message);
		return false;
	}
}

function createICNS(svgPath, icnsPath) {
	// ICNS is complex, use iconutil on macOS or skip
	log('ICNS creation requires macOS iconutil. Please create manually or on macOS.');
	log('Instructions:');
	log('1. Create icon.iconset folder with PNG files at sizes: 16, 32, 64, 128, 256, 512, 1024');
	log('2. Run: iconutil -c icns icon.iconset');
	return false;
}

function createLinuxPNGs(svgPath, outputDir) {
	if (!fs.existsSync(outputDir)) {
		fs.mkdirSync(outputDir, { recursive: true });
	}

	for (const size of ICON_SIZES.png) {
		const sizeDir = path.join(outputDir, `${size}x${size}`);
		if (!fs.existsSync(sizeDir)) {
			fs.mkdirSync(sizeDir, { recursive: true });
		}

		const pngPath = path.join(sizeDir, 'apps', 'hassanide.png');
		const appsDir = path.dirname(pngPath);
		if (!fs.existsSync(appsDir)) {
			fs.mkdirSync(appsDir, { recursive: true });
		}

		convertSVGtoPNG(svgPath, pngPath, size);
	}
	return true;
}

function main() {
	log('Converting Hassan IDE icons...\n');

	const sourceSVG = path.join(HASSANIDE_ICONS, 'logo.svg');

	if (!fs.existsSync(sourceSVG)) {
		console.error(`Source SVG not found: ${sourceSVG}`);
		process.exit(1);
	}

	if (!checkImageMagick()) {
		console.error('ImageMagick not found. Please install ImageMagick:');
		console.error('  Windows: Download from https://imagemagick.org/script/download.php');
		console.error('  macOS: brew install imagemagick');
		console.error('  Linux: sudo apt install imagemagick');
		console.error('');
		console.error('Or manually convert the icons:');
		console.error(`  Source: ${sourceSVG}`);
		console.error(`  Win32 ICO: ${path.join(WIN32_ICONS, 'hassanide.ico')}`);
		console.error(`  macOS ICNS: ${path.join(DARWIN_ICONS, 'Hassan IDE.icns')}`);
		console.error(`  Linux PNGs: ${LINUX_ICONS}/`);
		process.exit(1);
	}

	log(`Source: ${sourceSVG}`);
	log('');

	// Windows ICO
	log('Creating Windows ICO...');
	const icoPath = path.join(WIN32_ICONS, 'hassanide.ico');
	if (createICO(sourceSVG, icoPath)) {
		log(`  ✓ Created: ${icoPath}`);
	} else {
		log(`  ✗ Failed to create Windows ICO`);
	}

	// macOS ICNS
	log('Creating macOS ICNS...');
	const icnsPath = path.join(DARWIN_ICONS, 'Hassan IDE.icns');
	createICNS(sourceSVG, icnsPath);

	// Linux PNGs
	log('Creating Linux PNGs...');
	if (createLinuxPNGs(sourceSVG, LINUX_ICONS)) {
		log(`  ✓ Created Linux icons in: ${LINUX_ICONS}`);
	}

	log('');
	log('Icon conversion complete!');
	log('');
	log('Next steps:');
	log('1. Verify the generated icons look correct');
	log('2. For macOS, create ICNS file using iconutil on a Mac');
	log('3. Run the build: npm run gulp vscode-win32-x64');
}

main();
