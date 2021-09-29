<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class IsMaximumTableAttributesReachedValidator extends ConstraintValidator
{
    private AttributeRepositoryInterface $attributeRepository;
    private array $pendingTableAttributeCodes = [];

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

        $tableAttributeCodes = \array_unique(\array_merge(
            $this->attributeRepository->getAttributeCodesByType(AttributeTypes::TABLE),
            \array_keys($this->pendingTableAttributeCodes)
        ));
        if (\in_array($value->getCode(), $tableAttributeCodes)) {
            return;
        }

        if (\count($tableAttributeCodes) >= IsMaximumTableAttributesReached::LIMIT) {
            $this
                ->context
                ->buildViolation($constraint->message, ['{{ limit }}' => IsMaximumTableAttributesReached::LIMIT])
                ->addViolation();
        } else {
            $this->pendingTableAttributeCodes[$value->getCode()] = true;
        }
    }
}
