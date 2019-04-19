<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Family attribute_used_as_axis constraint.
 *
 * Checks that all attributes used as axis are also attributes of the family.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyAttributeUsedAsAxisValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($family, Constraint $constraint): void
    {
        if (!$constraint instanceof FamilyAttributeUsedAsAxis) {
            throw new UnexpectedTypeException($constraint, FamilyAttributeUsedAsAxis::class);
        }

        if (!$family instanceof FamilyInterface) {
            return;
        }

        if (!$constraint instanceof FamilyAttributeUsedAsAxis) {
            return;
        }

        foreach ($family->getFamilyVariants() as $familyVariant) {
            $missingAttributesUsedAsAxis = $this->getMissingAttributeCodesUsedAsAxis($family, $familyVariant);
            if (!empty($missingAttributesUsedAsAxis)) {
                $this->buildViolationsForMissingAttributesUsedAsAxis(
                    $constraint,
                    $familyVariant,
                    $missingAttributesUsedAsAxis
                );
            }
        }
    }

    /**
     * @param FamilyInterface        $family
     * @param FamilyVariantInterface $familyVariant
     *
     * @return string[]
     */
    private function getMissingAttributeCodesUsedAsAxis(
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant
    ): array {
        $attributeCodesUsedAsAxis = $familyVariant->getAxes()->map(
            function (AttributeInterface $attribute) {
                return $attribute->getCode();
            }
        )->toArray();

        return array_diff($attributeCodesUsedAsAxis, $family->getAttributeCodes());
    }

    /**
     * @param FamilyAttributeUsedAsAxis $constraint
     * @param FamilyVariantInterface    $familyVariant
     * @param AttributeInterface[]      $missingAttributeCodesUsedAsAxis
     */
    private function buildViolationsForMissingAttributesUsedAsAxis(
        FamilyAttributeUsedAsAxis $constraint,
        FamilyVariantInterface $familyVariant,
        array $missingAttributeCodesUsedAsAxis
    ): void {
        foreach ($missingAttributeCodesUsedAsAxis as $missingAttributeUsedAsAxis) {
            $this->context
                ->buildViolation($constraint->messageAttribute, [
                    '%attribute%'      => $missingAttributeUsedAsAxis,
                    '%family_variant%' => $familyVariant->getCode(),
                ])
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }
}
