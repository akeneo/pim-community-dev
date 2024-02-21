<?php

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 * Exception thrown when performing an action on a property with invalid data.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidPropertyException extends PropertyException
{
    const EXPECTED_CODE = 100;
    const DATE_EXPECTED_CODE = 101;

    const NOT_EMPTY_VALUE_EXPECTED_CODE = 200;

    const VALID_ENTITY_CODE_EXPECTED_CODE = 300;
    const VALID_GROUP_TYPE_EXPECTED_CODE = 301;
    const VALID_GROUP_EXPECTED_CODE = 302;
    const VALID_PATH_EXPECTED_CODE = 304;
    const VALID_DATA_EXPECTED_CODE = 305;

    /** @var string */
    protected $propertyValue;

    /** @var string */
    protected $className;

    /**
     * @param string          $propertyName
     * @param string          $propertyValue
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        $propertyName,
        $propertyValue,
        $className,
        $message = '',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->propertyName = $propertyName;
        $this->propertyValue = $propertyValue;
        $this->className = $className;
    }

    /**
     * @param string $message
     * @param string $className
     *
     * @return InvalidPropertyException
     */
    public static function expected($message, $className)
    {
        return new static(
            null,
            null,
            $className,
            $message
        );
    }

    /**
     * Build an exception when the data is empty and should not.
     *
     * @param string $propertyName
     * @param string $className
     *
     * @return InvalidPropertyException
     */
    public static function valueNotEmptyExpected($propertyName, $className)
    {
        $message = 'Property "%s" does not expect an empty value.';

        return new static(
            $propertyName,
            null,
            $className,
            sprintf($message, $propertyName),
            self::NOT_EMPTY_VALUE_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is an invalid entity code.
     *
     * @param string $propertyName
     * @param string $key
     * @param string $because
     * @param string $className
     * @param string $propertyValue
     *
     * @return InvalidPropertyException
     */
    public static function validEntityCodeExpected($propertyName, $key, $because, $className, $propertyValue)
    {
        $message = 'Property "%s" expects a valid %s. %s, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $key, $because, $propertyValue),
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the date is invalid.
     *
     * @param string $propertyName
     * @param string $format
     * @param string $className
     * @param string $propertyValue
     *
     * @return InvalidPropertyException
     */
    public static function dateExpected($propertyName, $format, $className, $propertyValue)
    {
        $message = 'Property "%s" expects a string with the format "%s" as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $format, $propertyValue),
            self::DATE_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the provided date is invalid
     *
     * @param string $propertyName
     * @param string $className
     * @param string $propertyValue
     *
     * @return InvalidPropertyException
     */
    public static function validDateExpected($propertyName, $className, $propertyValue)
    {
        $message = 'Property "%s" expects a valid date as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $propertyValue),
            self::DATE_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the group type is invalid or is not allowed.
     *
     * @param string $propertyName
     * @param string $because
     * @param string $className
     * @param string $propertyValue
     *
     * @return InvalidPropertyException
     */
    public static function validGroupTypeExpected($propertyName, $because, $className, $propertyValue)
    {
        $message = 'Property "%s" expects a valid group type. %s, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $because, $propertyValue),
            self::VALID_GROUP_TYPE_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the group is invalid or is not allowed.
     *
     * @param string $propertyName
     * @param string $because
     * @param string $className
     * @param string $propertyValue
     *
     * @return InvalidPropertyException
     */
    public static function validGroupExpected($propertyName, $because, $className, $propertyValue)
    {
        $message = 'Property "%s" expects a valid group. %s, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $because, $propertyValue),
            self::VALID_GROUP_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when a data is excepted.
     *
     * @param string $propertyName
     * @param string $data
     * @param string $className
     *
     * @return InvalidPropertyException
     */
    public static function dataExpected($propertyName, $data, $className)
    {
        $message = 'Property "%s" expects %s.';

        return new static(
            $propertyName,
            null,
            $className,
            sprintf($message, $propertyName, $data),
            self::VALID_DATA_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the pathname is invalid.
     *
     * @param string $propertyName
     * @param string $className
     * @param string $propertyValue
     *
     * @return InvalidPropertyException
     */
    public static function validPathExpected($propertyName, $className, $propertyValue)
    {
        $message = 'Property "%s" expects a valid pathname as data, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $propertyValue),
            self::VALID_PATH_EXPECTED_CODE
        );
    }

    /**
     * Build an exception from a previous one.
     *
     * @param string     $propertyName
     * @param string     $className
     * @param \Exception $exception
     *
     * @return InvalidPropertyException
     */
    public static function expectedFromPreviousException($propertyName, $className, \Exception $exception)
    {
        return new static(
            $propertyName,
            null,
            $className,
            $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
    }

    /**
     * @return string
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
