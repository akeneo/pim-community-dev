<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeGroups;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MaxAttributeGroupCountValidator extends ConstraintValidator
{
    public function __construct(
        private AttributeGroupRepositoryInterface $attributeGroupRepository,
        private int $maxAttributeGroupCount,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxAttributeGroupCount) {
            throw new UnexpectedTypeException($constraint, MaxAttributeGroupCount::class);
        }

        if (!$value instanceof AttributeGroupInterface) {
            return;
        }

        if (null !== $value->getId()) {
            return;
        }

        $attributeGroupCount = $this->attributeGroupRepository->countAll();
        if ($this->maxAttributeGroupCount <= $attributeGroupCount) {
            $this->context->buildViolation($constraint->message, [
                '{{ max }}' => $this->maxAttributeGroupCount,
            ])->addViolation();
        }
    }
}
