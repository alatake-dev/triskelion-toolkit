<?php
namespace Triskelion\TriskelionToolkit\Core;

/**
 * AssetsManager: El responsable de inyectar CSS/JS.
 * Centralizamos aquí para manejar versiones y dependencias.
 */
class AssetsManager {
	public static function enqueue_admin_styles() {
		// Solo cargamos si estamos en una página del plugin para no arruinar el admin de WP
		$screen = get_current_screen();
		if (strpos($screen->id, 'triskelion') === false) return;

		wp_enqueue_style(
			'tsk-admin-css',
			TSK_URL . 'assets/css/admin-style.css',
			[],
			TSK_VERSION
		);
	}
}