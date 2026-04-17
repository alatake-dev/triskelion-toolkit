<?php
return [
	'general_settings' => [
		'name'         => __( 'General Settings', TSK_DOMAIN ),
		'description'  => 'General settings for the plugin.',
		'class'        => \Triskelion\Toolkit\Modules\GeneralSettings\GeneralSettingsLoader::class,
		'is_core'      => true,
		'priority'     => 0,
		'icon'         => 'dashicons-admin-generic'
	],
	'code_showcase' => [
		'name'         => __( 'Code Showcase', TSK_DOMAIN ),
		'description'  => __( 'Display code snippets in a beautiful macOS-style terminal with multiple tabs and syntax highlighting.', TSK_DOMAIN ),
		'class'        => \Triskelion\Toolkit\Modules\CodeShowcase\CodeShowcaseBlockLoader::class,
		'is_core'      => false,
		'priority'     => 100,
		'icon'         => 'dashicons-editor-code'
	],
	'insight_cards' => [
		'name'         => __( 'Insight Cards', TSK_DOMAIN ),
		'description'  => __( 'Transform standard quotes into visual callouts for Tips, Warnings, and Ideas with custom styles.', TSK_DOMAIN ),
		'class'        => \Triskelion\Toolkit\Modules\InsightCards\InsightCardsLoader::class,
		'is_core'      => false,
		'priority'     => 101,
		'icon'         => 'dashicons-format-quote'
	],
	'diagnostic' => [
		'name'         => __( 'Logs & Diagnostic', TSK_DOMAIN ),
		'description'  => __( 'Monitor system health, view activity logs, and troubleshoot module performance.', TSK_DOMAIN ),
		'class'        => \Triskelion\Toolkit\Modules\Diagnostic\DiagnosticLoader::class,
		'is_core'      => true,
		'priority'     => 900,
		'icon'         => 'dashicons-rest-api' // O 'dashicons-visibility'
	],
];