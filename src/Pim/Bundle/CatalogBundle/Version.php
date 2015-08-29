<?php

namespace Pim\Bundle\CatalogBundle;

/**
 * PIM Version
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version
{
    /** @staticvar string */
    const VERSION = '1.4.0-BETA2';

    /** @staticvar string */
    const EDITION = 'CE';

    /** @staticvar string */
    const VERSION_CODENAME = '';

    /**
     * @return string
     */
    public static function getMajor()
    {
        $matches = [];
        preg_match('/^(?P<major>\d)/', self::VERSION, $matches);

        return $matches['major'];
    }

    /**
     * @return string
     */
    public static function getMinor()
    {
        $matches = [];
        preg_match('/^(?P<minor>\d.\d)/', self::VERSION, $matches);

        return $matches['minor'];
    }

    /**
     * @return string
     */
    public static function getPatch()
    {
        $matches = [];
        preg_match('/^(?P<patch>\d.\d.\d)/', self::VERSION, $matches);

        return $matches['patch'];
    }

    /**
     * @return string
     */
    public static function getStability()
    {
        $matches = [];
        preg_match('/^\d.\d.\d-(?P<stability>\w+)\d$/', self::VERSION, $matches);

        return (isset($matches['stability'])) ? $matches['stability'] : 'stable';
    }
}
