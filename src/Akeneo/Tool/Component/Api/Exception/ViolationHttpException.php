<?php

namespace Akeneo\Tool\Component\Api\Exception;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Http exception when validation failed on an entity
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViolationHttpException extends UnprocessableEntityHttpException
{
    /** @var string */
    protected $violations;

    /**
     * @param ConstraintViolationListInterface $violations
     * @param string                           $message
     * @param \Exception|null                  $previous
     * @param int                              $code
     */
    public function __construct(
        ConstraintViolationListInterface $violations,
        $message = 'Validation failed.',
        \Exception $previous = null,
        $code = 0
    ) {
        parent::__construct($message, $previous, $code);

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
