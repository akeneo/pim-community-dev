<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Helper;
/**
 * Helper to manage memory usage creating point on scripts
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class MemoryHelper
{
    /**
     * Stock memory usage on demand
     * @staticvar
     * @var array
     */
    protected static $memories = array();

    /**
     * Write a memory usage for a specific point
     * @param string $pointName
     *
     * @return string
     * @static
     */
    public static function writeValue($pointName)
    {
        self::addValue($pointName);

        return self::format(self::getLastValue($pointName));
    }

    /**
     * Add a memory usage for a specific point
     * @param string $pointName
     *
     * @static
     */
    public static function addValue($pointName)
    {
        self::$memories[$pointName][] = memory_get_usage(true);
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
     * Write a gap beetween the two last memory usage of a point
     * @param string $pointName
     *
     * @return string
     * @static
     */
    public static function writeGap($pointName)
    {
        $value1 = self::getLastValue($pointName);
        self::addValue($pointName);
        $value2 = self::getLastValue($pointName);
        $gap = $value2 - $value1;

        return self::format($gap);
    }

    /**
     * Get the last measured value for a specific point
     * @param string $pointName
     *
     * @return integer
     * @static
     */
    protected static function getLastValue($pointName)
    {
        return end(self::$memories[$pointName]);
    }

    /**
     * Get all values for a specific point
     * @param string $pointName
     *
     * @return array
     * @static
     */
    public static function getValues($pointName)
    {
        return self::$memories[$pointName];
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
     * Reset values for a defined point
     * @param string $pointName
     *
     * @static
     */
    public static function resetPoint($pointName)
    {
        // TODO : replace by unset ? -> requiring test update
        self::$memories[$pointName] = array();
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