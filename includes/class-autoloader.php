<?php
namespace Triskelion\Toolkit;



spl_autoload_register(function ($class) {
	$prefix = 'Triskelion\\Toolkit\\';
	$base_dir = TSK_PATH . 'includes/';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) return;

	$relative_class = substr($class, $len);

	// 1. Convertimos el Namespace en ruta de carpetas
	// Ejemplo: Modules\CodeConsole\Loader -> modules/code-console/class-loader.php

	$parts = explode('\\', $relative_class);
	$file_name = 'class-' . str_replace('_', '-', strtolower(array_pop($parts))) . '.php';

	$sub_path = '';
	foreach ($parts as $part) {
		$sub_path .= str_replace('_', '-', strtolower($part)) . '/';
	}

	$file = $base_dir . $sub_path . $file_name;

	if (file_exists($file)) {
		require $file;
	}
});

