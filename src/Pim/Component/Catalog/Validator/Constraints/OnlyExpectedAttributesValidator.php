<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
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
        $familyAttributes = $family->getAttributes();
        $levelAttributes = $this->attributesProvider->getAttributes($entity);

        foreach ($entity->getValuesForVariation()->getAttributes() as $modelAttribute) {
            if (!$familyAttributes->contains($modelAttribute)) {
                $this->context->buildViolation(
                    OnlyExpectedAttributes::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY, [
                    '%attribute%' => $modelAttribute->getCode(),
                    '%family%' => $family->getCode()
                ])->atPath('attribute')->addViolation();

                continue;
            }

            if (!in_array($modelAttribute, $levelAttributes)) {
                $this->context->buildViolation(
                    OnlyExpectedAttributes::ATTRIBUTE_UNEXPECTED, [
                    '%attribute%' => $modelAttribute->getCode()
                ])->atPath('attribute')->addViolation();
            }
        }
    }
}
