<?php

namespace Pim\Component\Catalog\Validator\Constraints;

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
    const MAXIMUM_AXES_NUMBER = 5;

    /** @var TranslatorInterface */
    private $translator;

    /** @var array */
    private $availableTypes;

    /**
     * @param TranslatorInterface $translator
     * @param array               $availableTypes
     */
    public function __construct(TranslatorInterface $translator, array $availableTypes)
    {
        $this->translator = $translator;
        $this->availableTypes = $availableTypes;
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

        $this->validateAxesAttributes($familyVariant);
        $this->validateAttributes($familyVariant);
        $this->validateNumberOfLevelAndAxis($familyVariant);
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
                ])->addViolation();
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
                ])->addViolation();
            }
        }

        if (count($attributeCodes) !== count(array_unique($attributeCodes))) {
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_attributes_unique');
            $this->context->buildViolation($message, [
                '%attributes%' => implode(',', array_diff_assoc($attributeCodes, array_unique($attributeCodes)))
            ])->addViolation();
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
                ])->addViolation();
            }

            if (!in_array($axis->getType(), $this->availableTypes)) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_attribute_type');
                $this->context->buildViolation($message, [
                    '%axis%' => $axis->getCode(),
                ])->addViolation();
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
                    ])->addViolation();
                }
            }
        }

        if (count($axisCodes) !== count(array_unique($axisCodes))) {
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_unique');
            $this->context->buildViolation($message, [
                '%attributes%' => implode(array_diff_assoc($axisCodes, array_unique($axisCodes))),
            ])->addViolation();
        }
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     */
    private function validateNumberOfLevelAndAxis(FamilyVariantInterface $familyVariant): void
    {
        $numberOfLevel = $familyVariant->getNumberOfLevel();
        $i = 0;
        while ($i !== $numberOfLevel) {
            $attributeSet = $familyVariant->getVariantAttributeSet($i + 1);

            if (null === $attributeSet) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_level_do_not_exist');
                $this->context->buildViolation($message, [
                    '%level%' => $i + 1,
                ])->addViolation();
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
