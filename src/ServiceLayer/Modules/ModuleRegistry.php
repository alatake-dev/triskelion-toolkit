<?php

namespace Triskelion\Toolkit\Modules;

use ReflectionClass;
use ReflectionException;

class ModuleRegistry {
	private static array $modules = [];
	/**
	 * Registra un módulo en el sistema.
	 * * @param string $id Identificador único (slug) del módulo.
	 * @param array $data Metadatos (name, class, priority, etc).
	 */
	public static function register( string $id, array $data ): void {
		self::$modules[$id] = $data;
	}
	public static function has_settings( string $class_name ): bool {
		try {
			// Forzamos el autoloader (true)
			if ( ! class_exists( $class_name, true ) ) {
				error_log("❌ Registry: Clase no encontrada: " . $class_name);
				return false;
			}

			$reflection = new ReflectionClass( $class_name );
			return $reflection->implementsInterface( \Triskelion\Toolkit\Core\SettingsProviderInterface::class );
		} catch ( ReflectionException $e ) {
			return false;
		}
	}
	public static function get_all(): array {
		uasort( self::$modules, function( $a, $b ) {
			$priority_a = $a['priority'] ?? 100;
			$priority_b = $b['priority'] ?? 100;
			return $priority_a <=> $priority_b;
		});
		return self::$modules;
	}
}