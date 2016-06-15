<?php

namespace Pim\Component\Connector\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Exception which may be thrown when we convert an array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArrayConversionException extends \LogicException
{
    /** @var ConstraintViolationListInterface */
    protected $violations;

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
