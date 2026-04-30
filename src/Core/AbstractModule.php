<?php

namespace Triskelion\TriskelionToolkit\Core;

abstract class AbstractModule {

	public function __construct() {
		// Disparamos el registro del módulo en los hooks de WP.
		$this->register();
	}

	/**
	 * Aquí es donde el módulo engancha sus ServiceLayer y ViewLayer.
	 */
	abstract protected function register();

}