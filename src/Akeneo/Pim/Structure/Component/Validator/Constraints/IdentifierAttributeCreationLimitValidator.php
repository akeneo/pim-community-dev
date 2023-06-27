<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierAttributeCreationLimitValidator extends ConstraintValidator
{
    public function __construct(private AttributeRepositoryInterface $repository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, IdentifierAttributeCreationLimit::class);

        if (\count($this->repository->findBy(['type' => AttributeTypes::IDENTIFIER])) >= $constraint->limit) {
            $this->context
                ->buildViolation($constraint->message, ['{{limit}}' => $constraint->limit])
                ->addViolation();
        }
    }
}
