<?php
/**
 * Plugin Name: Triskelion Toolkit
 * Description: Suite de utilerías modular para Triskelion y Alatake.
 * Version:     0.0.1
 * Author:      Alatake / Triskelion
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Si alguien intenta acceder directamente al archivo, adiós.
if ( ! defined( 'ABSPATH' ) ) exit;

// Definir constantes de ruta
define( 'TSK_PATH', plugin_dir_path( __FILE__ ) );
define( 'TSK_URL',  plugin_dir_url( __FILE__ ) );
const TSK_VERSION = '0.0.1';

require_once TSK_PATH . 'includes/class-autoloader.php';

// Inicializar el Toolkit
\Triskelion\Toolkit\Toolkit::init();

