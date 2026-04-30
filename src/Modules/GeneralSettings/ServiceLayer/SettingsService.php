<?php

namespace Triskelion\TriskelionToolkit\Modules\GeneralSettings\ServiceLayer;

class SettingsService {
	const OPTION_NAME = 'triskelion_modules_settings';

	public function __construct() {
		add_action('admin_init', [$this, 'register_toolkit_settings']);
	}

	public function register_toolkit_settings(): void {
		register_setting(
			'triskelion_settings_group',
			self::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => [$this, 'sanitize_modules'],
				'default'           => [],
			]
		);
	}

	public function sanitize_modules($input) {
		$sanitized = [];
		if (is_array($input)) {
			foreach ($input as $key => $value) {
				$sanitized[sanitize_key($key)] = (bool) $value;
			}
		}
		return $sanitized;
	}

}