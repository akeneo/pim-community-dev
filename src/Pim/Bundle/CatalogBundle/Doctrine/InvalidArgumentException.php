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

    /**
     * @param string $name
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayKeyExpected($name, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array with the key "%s" as data, "%s" given (for %s %s).',
                $name,
                $key,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $because
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayInvalidKey($name, $key, $because, $action, $type, $data)
    {
        $err = 'Attribute or field "%s" expects an array with valid data for the key "%s". %s, "%s" given (for %s %s).';

        return new self(sprintf($err, $name, $key, $because, $data, $action, $type));
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayNumericKeyExpected($name, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array with numeric data for the key "%s", "%s" given (for %s %s).',
                $name,
                $key,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringKeyExpected($name, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array with string data for the key "%s", "%s" given (for %s %s).',
                $name,
                $key,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param \Exception $exception
     * @param string     $name
     * @param string     $action
     * @param string     $type
     *
     * @return InvalidArgumentException
     */
    public static function expectedFromPreviousException(\Exception $exception, $name, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" excepts valid data, scope and locale (for %s %s). %s',
                $name,
                $action,
                $type,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
