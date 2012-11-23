<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Helper;
/**
 * Helper to manage memory usage creating events on scripts
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class MemoryHelper
{
    /**
     * Store for memory usage
     * @staticvar
     * @var array
     */
    protected static $memories = array();

    /**
     * Write a memory usage for a specific event
     * @param string $eventName
     *
     * @return string
     * @static
     */
    public static function writeValue($eventName)
    {
        self::addValue($eventName);

        return self::format(self::getLastValue($eventName));
    }

    /**
     * Add a memory usage for a specific event
     * @param string $eventName
     *
     * @static
     */
    public static function addValue($eventName)
    {
        self::$memories[$eventName][] = memory_get_usage(true);
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
        $value = $value / 1024 / 1024;

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
        return 'Mo';
    }

    /**
     * Write a gap beetween the two last memory usage of a event
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

        return self::format($value2) .' ('. self::format($gap) .')';
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
        return end(self::$memories[$eventName]);
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
        return self::$memories[$eventName];
    }

    /**
     * Get all memory values stored
     * @return array
     * @static
     */
    public static function getInstance()
    {
        return self::$memories;
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
        self::$memories[$eventName] = array();
    }

    /**
     * Reset all memory values stored
     * @static
     */
    public static function resetAllPoints()
    {
        self::$memories = array();
    }
}