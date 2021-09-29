<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class IsMaximumTableAttributesReachedValidator extends ConstraintValidator
{
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, IsMaximumTableAttributesReached::class);

        if (!$value instanceof AttributeInterface || $value->getType() !== AttributeTypes::TABLE) {
            return;
        }

        $tableAttributeCodes = $this->attributeRepository->getAttributeCodesByType(AttributeTypes::TABLE);

        if (in_array($value->getCode(), $tableAttributeCodes)) {
            return;
        }

        if (count($tableAttributeCodes) >= IsMaximumTableAttributesReached::LIMIT) {
            $this
                ->context
                ->buildViolation($constraint->message, ['{{ limit }}' => IsMaximumTableAttributesReached::LIMIT])
                ->addViolation();
        }
    }
}
