<?php

namespace Akeneo\Component\StorageUtils\Exception;

/**
 * Exception an updater can throw when updating a property with an unexpected data type.
 * For example, when a scalar is provided instead of an array.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidPropertyTypeException extends ObjectUpdaterException
{
    const EXPECTED_CODE = 100;
    const SCALAR_EXPECTED_CODE = 101;
    const ARRAY_EXPECTED_CODE = 102;
    const VALID_ARRAY_STRUCTURE_EXPECTED_CODE = 103;

    /** @var string */
    protected $propertyName;

    /** @var mixed */
    protected $propertyValue;

    /** @var string */
    protected $className;

    /**
     * @param string          $propertyName
     * @param mixed           $propertyValue
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($propertyName, $propertyValue, $className, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->propertyName  = $propertyName;
        $this->propertyValue = $propertyValue;
        $this->className = $className;
    }

    /**
     * Build an exception when the data is not a scalar value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that is not a scalar (array, object, null)
     *
     * @return InvalidPropertyTypeException
     */
    public static function scalarExpected($propertyName, $className, $propertyValue)
    {
        $message = 'Property "%s" expects a scalar.';

        return new self(
            $propertyName,
            $className,
            $propertyValue,
            sprintf($message, $propertyName),
            self::SCALAR_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data is not an array value.
     *
     * @param string $propertyName
     * @param string $className
     * @param mixed  $propertyValue a value that is not an array (scalar, object, null)
     *
     * @return InvalidPropertyTypeException
     */
    public static function arrayExpected($propertyName, $className, $propertyValue)
    {
        $message = 'Property "%s" expects an array.';

        return new self(
            $propertyName,
            $className,
            $propertyValue,
            sprintf($message, $propertyName),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * Build an exception when the data inside the array does not have the structure expected.
     * For example, when the array contains scalar values instead of array values.
     *
     * @param string $propertyName
     * @param string $because
     * @param string $className
     * @param array  $propertyValue
     *
     * @return InvalidPropertyTypeExceptionn
     */
    public static function validArrayStructureExpected($propertyName, $because, $className, array $propertyValue)
    {
        $message = 'Property "%s" expects a valid array, %s.';

        return new self(
            $propertyName,
            $className,
            $propertyValue,
            sprintf($message, $propertyName, $because),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }
}
