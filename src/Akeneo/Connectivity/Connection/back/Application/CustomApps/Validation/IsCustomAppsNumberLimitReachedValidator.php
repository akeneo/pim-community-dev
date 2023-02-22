<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\CustomApps\Validation;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\IsCustomAppsNumberLimitReachedQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCustomAppsNumberLimitReachedValidator extends ConstraintValidator
{
    public function __construct(private readonly IsCustomAppsNumberLimitReachedQueryInterface $isCustomAppsNumberLimitReachedQuery)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsCustomAppsNumberLimitReached) {
            throw new UnexpectedTypeException($constraint, IsCustomAppsNumberLimitReached::class);
        }

        if ($this->isCustomAppsNumberLimitReachedQuery->execute()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
