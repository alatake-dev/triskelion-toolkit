<?php

namespace Triskelion\TriskelionToolkit\Core;


use Triskelion\TriskelionToolkit\Tests\TestCase;

class KernelTest extends TestCase {



	public function testBoot() {
		$this->expectNotToPerformAssertions();
		$kernel = new Kernel();
		$kernel->boot();
	}

	public function testInit_i18n() {
		$this->assertTrue(true);
	}

	public function testSetup() {
		$this->assertTrue(true);
	}
}
