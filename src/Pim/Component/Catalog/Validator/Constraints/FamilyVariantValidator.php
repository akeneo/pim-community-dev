<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\FamilyVariant as FamilyVariantModel;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Symfony\Component\Translation\TranslatorInterface;
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
    const MAXIMUM_LEVEL_NUMBER = 2;

    const MAXIMUM_AXES_NUMBER = 5;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_no_level');
            $this->context->buildViolation($message)->atPath('variant_attribute_sets')->addViolation();
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
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_has_family_attribute');
                $this->context->buildViolation($message, [
                    '%attribute%' => $attribute->getCode(),
                    '%family%' => $family->getCode(),
                    '%family_variant%' => $familyVariant->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            if ($attribute->isUnique() &&
                null !== $lastLevelAttributeSet &&
                !$lastLevelAttributeSet->hasAttribute($attribute)
            ) {
                $message = $this->translator->trans(
                    'pim_catalog.constraint.family_variant_unique_attributes_in_last_level'
                );
                $this->context->buildViolation($message, [
                    '%attribute%' => $attribute->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }
        }

        if (count($attributeCodes) !== count(array_unique($attributeCodes))) {
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_attributes_unique');
            $this->context->buildViolation($message, [
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
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_wrong_type');
                $this->context->buildViolation($message, [
                    '%axis%' => $axis->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            if ($axis->isUnique()) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_attribute_type_unique');
                $this->context->buildViolation($message, [
                    '%axis%' => $axis->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            $availableTypes = FamilyVariantModel::getAvailableAxesAttributeTypes();
            if (!in_array($axis->getType(), $availableTypes)) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_attribute_type');
                $this->context->buildViolation($message, [
                    '%axis%' => $axis->getCode(),
                ])->atPath('variant_attribute_sets')->addViolation();
            }

            for ($level = 1; $level <= $familyVariant->getNumberOfLevel(); $level++) {
                $variantAttributeSet = $familyVariant->getVariantAttributeSet($level);
                if (null !== $variantAttributeSet &&
                    !$variantAttributeSet->getAxes()->contains($axis) &&
                    $variantAttributeSet->getAttributes()->contains($axis)
                ) {
                    $message = $this->translator->trans('pim_catalog.constraint.family_variant_axis_level');
                    $this->context->buildViolation($message, [
                        '%axis%' => $axis->getCode(),
                    ])->atPath('variant_attribute_sets')->addViolation();
                }
            }
        }

        if (count($axisCodes) !== count(array_unique($axisCodes))) {
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_unique');
            $this->context->buildViolation($message, [
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
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_maximum_number_of_level');
            $this->context
                ->buildViolation($message, ['%level%' => self::MAXIMUM_LEVEL_NUMBER,])
                ->atPath('variant_attribute_sets')
                ->addViolation();
        }

        $i = 0;
        while ($i !== $numberOfLevel) {
            $attributeSet = $familyVariant->getVariantAttributeSet($i + 1);

            if (null === $attributeSet) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_level_do_not_exist');
                $this->context->buildViolation($message, [
                    '%level%' => $i + 1,
                ])->atPath('variant_attribute_sets')->addViolation();
            } elseif (static::MAXIMUM_AXES_NUMBER < $attributeSet->getAxes()->count()) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_number_of_axes');
                $this->context->buildViolation($message)->addViolation();
            } elseif (0 === $attributeSet->getAxes()->count()) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_no_axis');
                $this->context->buildViolation($message, [
                    '%level%' => $i + 1,
                ])->addViolation();
            }

            $i++;
        }
    }
}
