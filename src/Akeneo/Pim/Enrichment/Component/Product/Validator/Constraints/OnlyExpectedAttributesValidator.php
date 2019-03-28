<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OnlyExpectedAttributesValidator extends ConstraintValidator
{
    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /**
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     */
    public function __construct(EntityWithFamilyVariantAttributesProvider $attributesProvider)
    {
        $this->attributesProvider = $attributesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            throw new UnexpectedTypeException($constraint, EntityWithFamilyVariantInterface::class);
        }

        if (!$constraint instanceof OnlyExpectedAttributes) {
            throw new UnexpectedTypeException($constraint, OnlyExpectedAttributes::class);
        }

        if (null === $entity->getFamilyVariant()) {
            return;
        }
        $family = $entity->getFamilyVariant()->getFamily();
        $familyAttributeCodes = $family->getAttributeCodes();
        $levelAttributes = $this->attributesProvider->getAttributes($entity);

        $levelAttributeCodes = array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $levelAttributes
        );

        foreach ($entity->getValuesForVariation()->getAttributeCodes() as $modelAttributeCode) {
            if (!in_array($modelAttributeCode, $familyAttributeCodes)) {
                $this->context->buildViolation(
                    OnlyExpectedAttributes::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY, [
                    '%attribute%' => $modelAttributeCode,
                    '%family%' => $family->getCode()
                ])->atPath('attribute')->addViolation();

                continue;
            }

            if (!in_array($modelAttributeCode, $levelAttributeCodes, true)) {
                $this->context->buildViolation(
                    OnlyExpectedAttributes::ATTRIBUTE_UNEXPECTED, [
                    '%attribute%' => $modelAttributeCode
                ])->atPath('attribute')->addViolation();
            }
        }
    }
}
