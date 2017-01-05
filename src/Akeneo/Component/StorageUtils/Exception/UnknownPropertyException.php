<?php

namespace Akeneo\Component\StorageUtils\Exception;

/**
 * Unknown property exception the updater can throw when updating value on an unknown property.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnknownPropertyException extends ObjectUpdaterException
{
    /** @var string */
    protected $propertyName;

    /**
     * @param string          $propertyName
     * @param string          $message
     * @param \Exception|null $previous
     * @param int             $code
     */
    public function __construct($propertyName, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->propertyName = $propertyName;
    }

    /**
     * @param string $propertyName
     * @param string $previous
     *
     * @return UnknownPropertyException
     */
    public static function unknownProperty($propertyName, $previous)
    {
        return new self(
            $propertyName,
            sprintf(
                'Property "%s" does not exist.',
                $propertyName
            ),
            0,
            $previous
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
