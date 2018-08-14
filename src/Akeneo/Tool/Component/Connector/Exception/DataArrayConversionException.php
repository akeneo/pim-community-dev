<?php

namespace Akeneo\Tool\Component\Connector\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Exception thrown during a conversion of an array.
 * Related to a data problem, for example, a property has not been filled as expected.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataArrayConversionException extends ArrayConversionException
{
    /** @var ConstraintViolationListInterface */
    protected $violations;

    /**
     * @param string                                $message
     * @param int                                   $code
     * @param \Exception|null                       $previous
     * @param ConstraintViolationListInterface|null $violations
     */
    public function __construct(
        $message,
        $code = 0,
        \Exception $previous = null,
        ConstraintViolationListInterface $violations = null
    ) {
        $this->violations = $violations;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
