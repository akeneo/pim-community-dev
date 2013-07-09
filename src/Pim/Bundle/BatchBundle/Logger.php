<?php
namespace Pim\Bundle\BatchBundle;

/**
 * Logger
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Logger
{
    const DEBUG = 'DEBUG';
    const INFO  = 'INFO';
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';

    public static function debug($message, $e = null)
    {
        self::log(self::DEBUG, $message, $e);
    }

    public static function info($message, $e = null)
    {
        self::log(self::INFO, $message, $e);
    }

    public static function error($message, $e = null)
    {
        self::log(self::ERROR, $message, $e);
    }

    public static function warning($message, $e = null)
    {
        self::log(self::WARNING, $message, $e);
    }

    public static function log($level, $message, $e = null)
    {
        if ($e != null) {
            $message .= ':'.$e->getMessage()."\n".$e->getTraceAsString();
        }
        echo sprintf("[%s] %s\n", $level, $message);
    }
}
