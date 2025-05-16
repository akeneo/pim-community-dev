<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
    public function __construct(
        private readonly AttributeRepositoryInterface $repository,
        private readonly int $creationLimit
    ) {
    }

    public function validate($attribute, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, IdentifierAttributeCreationLimit::class);
        if (!$attribute instanceof AttributeInterface) {
            return;
        }
        if (null !== $attribute->getId() || AttributeTypes::IDENTIFIER !== $attribute->getType()) {
            return;
        }

        if ($this->creationLimit <= \count($this->repository->getAttributeCodesByType(AttributeTypes::IDENTIFIER))) {
            $this->context
                ->buildViolation($constraint->message, ['{{limit}}' => $this->creationLimit])
                ->addViolation();
        }
    }
}
