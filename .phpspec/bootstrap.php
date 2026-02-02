<?php

declare(strict_types=1);

/**
 * Bootstrap file for phpspec to suppress PHP 8.3 deprecation warnings
 * from vendor libraries (phpspec, prophecy, etc.) that use implicit nullable types.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
