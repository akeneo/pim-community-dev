<?php

namespace Akeneo\Component\StorageUtils\Exception;

/**
 *  Exception an updater can throw when updating a property that can't be modified.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImmutablePropertyException extends ObjectUpdaterException
{
    /** @var string */
    protected $propertyName;

    /** @var string */
    protected $propertyValue;

    /**
     * @param string          $propertyName
     * @param string          $propertyValue
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($propertyName, $propertyValue, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->propertyName  = $propertyName;
        $this->propertyValue = $propertyValue;
    }

    /**
     * @param string $propertyName
     * @param string $propertyValue
     * @param string $action
     * @param string $type
     *
     * @return ImmutablePropertyException
     */
    public static function immutableProperty($propertyName, $propertyValue, $action, $type)
    {
        return new self(
            $propertyName,
            $propertyValue,
            sprintf(
                'Property "%s" cannot be modified, "%s" given (for %s %s).',
                $propertyName,
                $propertyValue,
                $action,
                $type
            )
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
