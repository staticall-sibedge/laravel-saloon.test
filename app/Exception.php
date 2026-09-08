<?php declare(strict_types=1);

namespace App;

use Exception as BaseException;

/**
 * Application-wide abstract exception class
 * You must use custom exception class for a particular case, instead of this abstract one
 */
abstract class Exception extends BaseException {}
