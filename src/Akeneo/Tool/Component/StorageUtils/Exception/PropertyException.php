<?php

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 * This exception is the root exception when performing an action that failed on a property.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class PropertyException extends \LogicException
{
    /** @var string */
    protected $propertyName;

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }
}
