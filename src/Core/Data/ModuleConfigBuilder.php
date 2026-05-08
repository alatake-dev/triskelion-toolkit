<?php
/**
 * Fluent Builder for ModuleConfig objects.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Data
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Data;

use InvalidArgumentException;

/**
 * Class ModuleConfigBuilder
 *
 * Provides a fluent interface to construct immutable ModuleConfig instances.
 * This ensures mandatory fields are validated before instantiation.
 *
 * @package Triskelion\TriskelionToolkit\Core\Data
 */
class ModuleConfigBuilder {
	/**
	 * Temporary storage for module configuration data.
	 *
	 * @var array<string, mixed>
	 */
	private array $data = array();


	/**
	 * Sets the unique module identifier.
	 *
	 * @param string $id Unique ID.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If mandatory field id are missing.
	 */
	public function set_id( string $id ): self {
		if ( empty( $id ) ) {
			throw new InvalidArgumentException( 'Module ID cannot be empty.' );
		}
		$this->data['id'] = $id;

		return $this;
	}

	/**
	 * Sets the module display name.
	 *
	 * @param string $name UI Name.
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->data['name'] = $name;

		return $this;
	}

	/**
	 * Sets the module description.
	 *
	 * @param string $desc Short explanation.
	 * @return self
	 */
	public function set_description( string $desc ): self {
		$this->data['description'] = $desc;

		return $this;
	}

	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	/**
	 * Sets the loader class name.
	 *
	 * @param string $class Fully Qualified Class Name.
	 * @return self
	 */
	public function set_class( string $class ): self {
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$this->data['class'] = $class;

		return $this;
	}

	/**
	 * Sets the execution priority.
	 *
	 * @param int $priority Priority level.
	 * @return self
	 */
	public function set_priority( int $priority ): self {
		$this->data['priority'] = $priority;

		return $this;
	}

	/**
	 * Sets whether the module is a core component.
	 *
	 * @param bool $is_core Core flag.
	 * @return self
	 */
	public function set_is_core( bool $is_core ): self {
		$this->data['is_core'] = $is_core;

		return $this;
	}

	/**
	 * Sets the Dashicon class for the module.
	 *
	 * @see https://developer.wordpress.org/resource/dashicons/
	 *
	 * @param string $icon Dashicon slug.
	 * @return self
	 */
	public function set_icon( string $icon ): self {
		$this->data['icon'] = $icon;

		return $this;
	}

	/**
	 * Validates and constructs the ModuleConfig object.
	 *
	 * @throws InvalidArgumentException If mandatory fields (id, class) are missing.
	 * @return ModuleConfig
	 * @since 1.0.0
	 */
	public function build(): ModuleConfig {
		// Validación mínima: si no hay ID o Clase, esto no va a arrancar.
		if ( empty( $this->data['id'] ) || empty( $this->data['class'] ) ) {
			throw new InvalidArgumentException( 'ModuleConfigBuilder: ID and Class are mandatory.' );
		}

		return new ModuleConfig(
			id: $this->data['id'],
			name: $this->data['name'] ?? $this->data['id'],
			description: $this->data['description'] ?? '',
			class: $this->data['class'],
			priority: $this->data['priority'] ?? 500,
			is_core: $this->data['is_core'] ?? false,
			icon: $this->data['icon'] ?? 'dashicons-admin-generic'
		);
	}
}
