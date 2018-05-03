<?php

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 * Exception thrown when performing an action on an unknown property.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnknownPropertyException extends PropertyException
{
    /**
     * @param string          $propertyName
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($propertyName, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->propertyName = $propertyName;
    }

    /**
     * @param string          $propertyName
     * @param \Exception|null $previous
     *
     * @return UnknownPropertyException
     */
    public static function unknownProperty($propertyName, \Exception $previous = null)
    {
        return new static(
            $propertyName,
            sprintf(
                'Property "%s" does not exist.',
                $propertyName
            ),
            0,
            $previous
        );
    }
}
