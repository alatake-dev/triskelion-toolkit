<?php

namespace Triskelion\Toolkit\Core;

interface SettingsProviderInterface {
	public function render_module_settings(): void;
	public function register_module_settings(): void;

	public function sanitize_module_settings( $input );
}