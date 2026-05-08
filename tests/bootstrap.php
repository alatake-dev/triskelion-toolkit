<?php
/**
 * PHPUnit Bootstrap para Triskelion Toolkit
 */

// Cargar el autoload de Composer
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Inicializar el motor de Mocks de WordPress
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();