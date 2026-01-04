/*---------------------------------------------------------------------------------------------
 *  Hassan IDE Build Configuration
 *  This file contains build settings for Hassan IDE
 *--------------------------------------------------------------------------------------------*/

export const hassanideConfig = {
	// Application Info
	name: 'Hassan IDE',
	shortName: 'hassanide',
	version: '1.0.0',
	description: 'Hassan IDE - محرر الأكواد العربي الاحترافي',

	// Company Info
	publisher: 'Hassan Tech',
	copyright: 'Copyright © 2024-2026 Hassan Tech. All rights reserved.',
	website: 'https://hassanide.com',

	// License API
	licenseApi: {
		baseUrl: 'https://hassanide.com/api',
		validateEndpoint: '/license-v2.php',
		activateEndpoint: '/license-v2.php',
		offlineGraceDays: 7
	},

	// Plans
	plans: {
		starter: {
			name: 'Starter',
			price: 0,
			features: ['basic_editor', 'terminal', 'git_basic'],
			extensions: 5,
			devices: 1
		},
		pro: {
			name: 'Pro',
			price: 29,
			features: ['all_starter', 'ai_assistant', 'templates', 'cloud_sync', 'all_packs', 'unlimited_extensions', 'auto_updates', 'hassan_panel'],
			devices: 3
		},
		teams: {
			name: 'Teams',
			price: 99,
			features: ['all_pro', 'team_dashboard', 'permissions', 'priority_support', 'invoice'],
			devices: 10
		}
	},

	// Colors (Brand)
	colors: {
		primary: '#4F46E5',
		secondary: '#7C3AED',
		lightPurple: '#818CF8',
		accent: '#A78BFA'
	}
};

export default hassanideConfig;
