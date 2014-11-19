<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

/**
 * Invalid argument exceptions the updater can throw when performing an action.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param string $name
     * @param string $expected
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function expected($name, $expected, $action, $type)
    {
        return new self(
            sprintf('Attribute or field "%s" expects %s as data (for %s %s).', $name, $expected, $action, $type)
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function booleanExpected($name, $action, $type)
    {
        return new self(
            sprintf('Attribute or field "%s" expects a boolean as data (for %s %s).', $name, $action, $type)
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function integerExpected($name, $action, $type)
    {
        return new self(
            sprintf('Attribute or field "%s" expects an integer as data (for %s %s).', $name, $action, $type)
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function numericExpected($name, $action, $type)
    {
        return new self(
            sprintf('Attribute or field "%s" expects a numeric as data (for %s %s).', $name, $action, $type)
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function stringExpected($name, $action, $type)
    {
        return new self(
            sprintf('Attribute or field "%s" expects a string as data (for %s %s).', $name, $action, $type)
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function arrayExpected($name, $action, $type)
    {
        return new self(
            sprintf('Attribute or field "%s" expects an array as data (for %s %s).', $name, $action, $type)
        );
    }
}
