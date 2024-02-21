<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierGeneratorCreationLimitValidator extends ConstraintValidator
{
    public function __construct(private IdentifierGeneratorRepository $repository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, IdentifierGeneratorCreationLimit::class);

        if ($this->repository->count() >= $constraint->limit) {
            $this->context
                ->buildViolation($constraint->message, ['{{limit}}' => $constraint->limit])
                ->addViolation();
        }
    }
}
