<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * @param FamilyVariantInterface $familyVariant
     */
    public function validate($familyVariant, Constraint $constraint)
    {
        if (!$familyVariant instanceof FamilyVariantInterface) {
            throw new UnexpectedTypeException($constraint, FamilyVariantInterface::class);
        }

        if (!$constraint instanceof FamilyVariant) {
            throw new UnexpectedTypeException($constraint, FamilyVariant::class);
        }

        $this->validateAxes($familyVariant->getAxes());
        $this->validateAttributes($familyVariant->getAttributes());
    }

    /**
     * Validate the attribute set attributes
     *
     * @param ArrayCollection $attributes
     */
    private function validateAttributes(ArrayCollection $attributes)
    {
        $attributeCodes = $attributes->map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();

        if (count($attributeCodes) !== count(array_unique($attributeCodes))) {
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_attributes_unique');
            $this->context->buildViolation($message)->addViolation();
        }
    }

    /**
     * Validate the attribute set axis
     *
     * @param ArrayCollection $axes
     */
    private function validateAxes(ArrayCollection $axes)
    {
        $availableTypes = [
            AttributeTypes::METRIC,
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::BOOLEAN,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        ];

        $axisCode = [];
        foreach ($axes as $axis) {
            $axisCode[] = $axis->getCode();
            if (!in_array($axis->getType(), $availableTypes)) {
                $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_type');
                $this->context->buildViolation($message)->addViolation();
            }
        }

        if (count($axisCode) !== count(array_unique($axisCode))) {
            $message = $this->translator->trans('pim_catalog.constraint.family_variant_axes_unique');
            $this->context->buildViolation($message)->addViolation();
        }
    }
}
