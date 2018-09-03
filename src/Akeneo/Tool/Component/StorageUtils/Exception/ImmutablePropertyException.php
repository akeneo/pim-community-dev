<?php

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 *  Exception thrown when performing an action on a property that can't be modified.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImmutablePropertyException extends PropertyException
{
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

        $this->propertyName  = $propertyName;
        $this->propertyValue = $propertyValue;
        $this->className = $className;
    }

    /**
     * @param string $propertyName
     * @param string $propertyValue
     * @param string $className
     *
     * @return ImmutablePropertyException
     */
    public static function immutableProperty($propertyName, $propertyValue, $className)
    {
        if (null === $propertyValue) {
            $propertyValue = 'NULL';
        }

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf(
                'Property "%s" cannot be modified, "%s" given.',
                $propertyName,
                $propertyValue
            )
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
