<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedValidator extends ConstraintValidator
{
    public function __construct(private IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsConnectionsNumberLimitReached) {
            throw new UnexpectedTypeException($constraint, IsConnectionsNumberLimitReached::class);
        }

        if ($this->isConnectionsNumberLimitReachedQuery->execute()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
