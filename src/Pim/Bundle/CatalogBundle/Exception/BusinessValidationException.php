<?php

namespace Pim\Bundle\CatalogBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Business validation exception
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BusinessValidationException extends \InvalidArgumentException
{
    /** @var ConstraintViolationListInterface $violations */
    protected $violations;

    /**
     * @param ConstraintViolationListInterface $violations The violation list
     * @param string                           $message    The Exception message to throw.
     * @param int                              $code       The Exception code.
     * @param \Exception                       $previous   The previous exception used for the exception chaining
     */
    public function __construct(
        ConstraintViolationListInterface $violations,
        $message = "",
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->violations = $violations;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
