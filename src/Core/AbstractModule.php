<?php

namespace Triskelion\TriskelionToolkit\Core;

abstract class AbstractModule {

	public function __construct() {
		$this->register();
	}

	abstract protected function register();


}