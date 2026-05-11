<?php

namespace Triskelion\TriskelionToolkit\Core\Enums;

enum LogLevel: int {
	case TRACE = 100;
	case DEBUG = 200;
	case INFO  = 300;
	case WARN  = 400;
	case ERROR = 500;
	case OFF   = 1000;

	public function satisfies( LogLevel $threshold ): bool {
		return $this->value >= $threshold->value;
	}
}
