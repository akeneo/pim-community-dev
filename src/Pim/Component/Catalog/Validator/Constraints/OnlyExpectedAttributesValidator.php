<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\FamilyVariant\CanHaveFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\CanHaveFamilyVariantInterface;
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
    /** @var CanHaveFamilyVariantAttributesProvider */
    private $attributesProvider;

    /**
     * @param CanHaveFamilyVariantAttributesProvider $attributesProvider
     */
    public function __construct(CanHaveFamilyVariantAttributesProvider $attributesProvider)
    {
        $this->attributesProvider = $attributesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$entity instanceof CanHaveFamilyVariantInterface) {
            throw new UnexpectedTypeException($constraint, CanHaveFamilyVariantInterface::class);
        }

        if (!$constraint instanceof OnlyExpectedAttributes) {
            throw new UnexpectedTypeException($constraint, OnlyExpectedAttributes::class);
        }

        if (null === $entity->getFamilyVariant()) {
            return;
        }

        $levelAttributes = $this->attributesProvider->getAttributes($entity);

        foreach ($entity->getAttributes() as $modelAttribute) {
            if (!in_array($modelAttribute, $levelAttributes)) {
                $this->context->buildViolation(
                    OnlyExpectedAttributes::ATTRIBUTE_UNEXPECTED, [
                    '%attribute%' => $modelAttribute->getCode()
                ])->addViolation();
            }
        }
    }
}
