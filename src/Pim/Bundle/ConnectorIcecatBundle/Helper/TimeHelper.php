<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Helper;
/**
 * Helper to manage time creating events on scripts
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TimeHelper
{
/**
     * Store for timestamp
     * @staticvar
     * @var array
     */
    protected static $times = array();

    /**
     * Write microtime for a specific event
     * @param string $eventName
     *
     * @return string
     * @static
     *
     * TODO : Useless ?!
     */
    public static function writeValue($eventName)
    {
        self::addValue($eventName);

        return self::format(self::getLastValue($eventName));
    }

    /**
     * Add microtime for a specific event
     * @param string $eventName
     *
     * @static
     */
    public static function addValue($eventName)
    {
        self::$times[$eventName][] = microtime(true);
    }

    /**
     * Format content to be written
     * @param string $value
     *
     * @return string
     * @static
     */
    protected static function format($value)
    {
        return $value .' '. self::getMeasure();
    }

    /**
     * Get measure as string
     *
     * @return string
     * @static
     */
    protected static function getMeasure()
    {
        return 'secs';
    }

    /**
     * Write a gap beetween the two last microtime of a event
     * @param string $eventName
     *
     * @return string
     * @static
     */
    public static function writeGap($eventName)
    {
        $value1 = self::getLastValue($eventName);
        self::addValue($eventName);
        $value2 = self::getLastValue($eventName);
        $gap = $value2 - $value1;

        return self::format($gap);
    }

    /**
     * Get the last measured value for a specific event
     * @param string $eventName
     *
     * @return integer
     * @static
     */
    protected static function getLastValue($eventName)
    {
        return end(self::$times[$eventName]);
    }

    /**
     * Get all values for a specific event
     * @param string $eventName
     *
     * @return array
     * @static
     */
    public static function getValues($eventName)
    {
        return self::$times[$eventName];
    }

    /**
     * Get all time values stored
     * @return array
     * @static
     */
    public static function getInstance()
    {
        return self::$times;
    }

    /**
     * Reset values for a defined event
     * @param string $eventName
     *
     * @static
     */
    public static function resetPoint($eventName)
    {
        // TODO : replace by unset ? -> requiring test update
        self::$times[$eventName] = array();
    }

    /**
     * Reset all time values stored
     * @static
     */
    public static function resetAllPoints()
    {
        self::$times = array();
    }
}