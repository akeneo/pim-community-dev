<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 *  Exception thrown when performing an action on a object where a mandatory property is missing.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MandatoryPropertyException extends PropertyException
{
    /** @var string */
    protected $propertyName;

    /** @var string */
    protected $className;

    /**
     * @param string          $propertyName
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $propertyName,
        string $className,
        string $message = '',
        int $code = 0,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->propertyName  = $propertyName;
        $this->className = $className;
    }

    /**
     * @param string $propertyName
     * @param string $className
     *
     * @return MandatoryPropertyException
     */
    public static function mandatoryProperty($propertyName, $className)
    {
        return new static(
            $propertyName,
            $className,
            sprintf(
                'Property "%s" is mandatory.',
                $propertyName
            )
        );
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
