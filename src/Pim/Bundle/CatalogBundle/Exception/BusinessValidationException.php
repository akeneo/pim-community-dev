<?php

namespace Pim\Bundle\CatalogBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Business validation exception
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BusinessValidationException extends \LogicException
{
    /** @var ConstraintViolationListInterface */
    protected $violations;

    /**
     * @param ConstraintViolationListInterface $violations
     * @param int                              $message
     * @param int                              $code
     * @param null                             $previous
     */
    public function __construct(ConstraintViolationListInterface $violations, $message, $code = 0, $previous = null)
    {
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
