<?php

namespace Akeneo\Component\StorageUtils\Exception;

/**
 * Immutable property exception the updater can throw when updating a property which can't be modified.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
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
     * @param \Exception|null $previous
     * @param int             $code
     */
    public function __construct($propertyName, $propertyValue, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->propertyName = $propertyName;
    }

    /**
     * @param $propertyName
     * @param $propertyValue
     * @param $action
     * @param $type
     *
     * @return ImmutablePropertyException
     */
    public static function immutableProperty($propertyName, $propertyValue, $action, $type)
    {
        return new self(
            $propertyName,
            $propertyValue,
            sprintf(
                'Property "%s" could not be modified, "%s" given (for %s %s).',
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
}
