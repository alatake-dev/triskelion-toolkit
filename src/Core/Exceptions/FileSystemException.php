<?php
/**
 * File System Exception File.
 *
 * Handles specialized errors related to file system operations within the toolkit.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Exceptions
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Exceptions;

use Exception;

/**
 * Class FileSystemException
 *
 * Custom exception class for handling file-related failures, such as missing
 * directories, permission issues, or failed write operations.
 *
 * @package Triskelion\TriskelionToolkit\Core\Exceptions
 */
class FileSystemException extends Exception {

}
