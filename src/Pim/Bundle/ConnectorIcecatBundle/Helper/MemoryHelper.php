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
     * @param string $point
     *
     * @return string
     * @static
     */
    public static function writePoint($point)
    {
        self::addPoint($point);

        return self::format(self::getLastPoint($point));
    }

    /**
     * Add a memory usage for a specific point
     * @param string $point
     *
     * @static
     */
    protected static function addPoint($point)
    {
        self::$memories[$point][] = memory_get_usage(true);
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
     * Write a gap beetween the two last memory usage
     * @param string $point
     *
     * @return string
     * @static
     */
    public static function writeGap($point)
    {
        $point1 = self::getLastPoint($point);
        self::addPoint($point);
        $point2 = self::getLastPoint($point);
        $gap = $point2 - $point1;

        return self::format($gap);
    }

    /**
     * Get the last measured point
     * @param string $point
     *
     * @return integer
     * @static
     */
    protected static function getLastPoint($point)
    {
        return end(self::$memories[$point]);
    }
}