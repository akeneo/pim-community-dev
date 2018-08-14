<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\FamilyVariant as FamilyVariantModel;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Family variant validator, it checks that attribute sets are well built.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantValidator extends ConstraintValidator
{
    private const MAXIMUM_LEVEL_NUMBER = 2;

    private const MAXIMUM_AXES_NUMBER = 5;

    /**
     * {@inheritdoc}
     *
     * @param FamilyVariantInterface $familyVariant
     */
    public function validate($familyVariant, Constraint $constraint): void
    {
        if (!$familyVariant instanceof FamilyVariantInterface) {
            throw new UnexpectedTypeException($constraint, FamilyVariantInterface::class);
        }

        if (!$constraint instanceof FamilyVariant) {
            throw new UnexpectedTypeException($constraint, FamilyVariant::class);
        }

        $validateAttributesSets = true;

        if (0 === $familyVariant->getNumberOfLevel()) {
            $this->context
                ->buildViolation(FamilyVariant::FAMILY_VARIANT_NO_LEVEL)
                ->atPath('variant_attribute_sets')
                ->addViolation();
            $validateAttributesSets = false;
        }

        // handled by another constraint
        if (null === $familyVariant->getFamily()) {
            $validateAttributesSets = false;
        }

        if (true === $validateAttributesSets) {
            $this->validateAxesAttributes($familyVariant);
            $this->validateAttributes($familyVariant);
            $this->validateNumberOfLevelAndAxis($familyVariant);
        }
    }

    /**
     * Validate the attribute set attributes
     *
     * @param FamilyVariantInterface $familyVariant
     */
    private function validateAttributes(FamilyVariantInterface $familyVariant): void
    {
        $family = $familyVariant->getFamily();
        $attributeCodes = [];
        $lastLevelAttributeSet = $familyVariant->getVariantAttributeSet($familyVariant->getNumberOfLevel());

        foreach ($familyVariant->getAttributes() as $attribute) {
            $attributeCodes[] = $attribute->getCode();

            if (!$family->hasAttribute($attribute)) {
                $this->context->buildViolation(FamilyVariant::HAS_FAMILY_ATTRIBUTE, [
                    '%attribute%' => $attribute->getCode(),
                    '%family%' => $family->getCode(),
                    '%family_variant%' => $familyVariant->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            if ($attribute->isUnique() &&
                null !== $lastLevelAttributeSet &&
                !$lastLevelAttributeSet->hasAttribute($attribute)
            ) {
                $this->context->buildViolation(FamilyVariant::UNIQUE_ATTRIBUTE_IN_LAST_LEVEL, [
                    '%attribute%' => $attribute->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }
        }

        if (count($attributeCodes) !== count(array_unique($attributeCodes))) {
            $this->context->buildViolation(FamilyVariant::ATTRIBUTES_UNIQUE, [
                '%attributes%' => implode(',', array_diff_assoc($attributeCodes, array_unique($attributeCodes)))
            ])->atPath('variant_attribute_sets')->addViolation();
        }
    }

    /**
     * Validate the attribute set axis
     *
     * @param FamilyVariantInterface $familyVariant
     */
    private function validateAxesAttributes(FamilyVariantInterface $familyVariant): void
    {
        $axes = $familyVariant->getAxes();

        $axisCodes = [];
        foreach ($axes as $axis) {
            $axisCodes[] = $axis->getCode();
            if ($axis->isLocalizable() || $axis->isScopable() || $axis->isLocaleSpecific()) {
                $this->context->buildViolation(FamilyVariant::AXES_WRONG_TYPE, [
                    '%axis%' => $axis->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            if ($axis->isUnique()) {
                $this->context->buildViolation(FamilyVariant::AXES_ATTRIBUTE_TYPE_UNIQUE, [
                    '%axis%' => $axis->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            $availableTypes = FamilyVariantModel::getAvailableAxesAttributeTypes();
            if (!in_array($axis->getType(), $availableTypes)) {
                $this->context->buildViolation(FamilyVariant::AXES_ATTRIBUTE_TYPE, [
                    '%axis%' => $axis->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            for ($level = 1; $level <= $familyVariant->getNumberOfLevel(); $level++) {
                $variantAttributeSet = $familyVariant->getVariantAttributeSet($level);
                if (null !== $variantAttributeSet &&
                    !$variantAttributeSet->getAxes()->contains($axis) &&
                    $variantAttributeSet->getAttributes()->contains($axis)
                ) {
                    $this->context->buildViolation(FamilyVariant::AXES_LEVEL, [
                        '%axis%' => $axis->getCode(),
                    ])->atPath('variant_attribute_sets')->addViolation();
                }
            }
        }

        if (count($axisCodes) !== count(array_unique($axisCodes))) {
            $this->context->buildViolation(FamilyVariant::AXES_UNIQUE, [
                '%attributes%' => implode(array_diff_assoc($axisCodes, array_unique($axisCodes))),
            ])->atPath('variant_attribute_sets')->addViolation();
        }
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     */
    private function validateNumberOfLevelAndAxis(FamilyVariantInterface $familyVariant): void
    {
        $numberOfLevel = $familyVariant->getNumberOfLevel();

        if (self::MAXIMUM_LEVEL_NUMBER < $numberOfLevel) {
            $this->context
                ->buildViolation(
                    FamilyVariant::MAXIMUM_NUMBER_OF_LEVEL,
                    ['%level%' => self::MAXIMUM_LEVEL_NUMBER]
                )
                ->atPath('variant_attribute_sets')
                ->addViolation();
        }

        $i = 0;
        while ($i !== $numberOfLevel) {
            $attributeSet = $familyVariant->getVariantAttributeSet($i + 1);

            if (null === $attributeSet) {
                $this->context
                    ->buildViolation(
                        FamilyVariant::LEVEL_DO_NOT_EXIST,
                        ['%level%' => $i + 1]
                    )
                    ->atPath('variant_attribute_sets')
                    ->addViolation();
            } elseif (static::MAXIMUM_AXES_NUMBER < $attributeSet->getAxes()->count()) {
                $this->context
                    ->buildViolation(
                        FamilyVariant::NUMBER_OF_AXES,
                        ['%max_axes_number%' => static::MAXIMUM_AXES_NUMBER]
                    )
                    ->atPath('variant_attribute_sets')
                    ->addViolation();
            } elseif (0 === $attributeSet->getAxes()->count()) {
                $this->context
                    ->buildViolation(FamilyVariant::NO_AXIS, ['%level%' => $i + 1])
                    ->atPath('variant_attribute_sets')
                    ->addViolation();
            }

            $i++;
        }
    }
}
