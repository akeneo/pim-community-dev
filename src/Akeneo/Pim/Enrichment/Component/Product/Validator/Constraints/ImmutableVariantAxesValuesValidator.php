<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that the variant axis values cannot be modified once set.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImmutableVariantAxesValuesValidator extends ConstraintValidator
{
    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var WriteValueCollectionFactory */
    private $valueCollectionFactory;

    /**
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param WriteValueCollectionFactory $valueCollectionFactory
     */
    public function __construct(
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        WriteValueCollectionFactory $valueCollectionFactory
    ) {
        $this->attributesProvider = $attributesProvider;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            throw new UnexpectedTypeException($constraint, EntityWithFamilyVariantInterface::class);
        }

        if (!$constraint instanceof ImmutableVariantAxesValues) {
            throw new UnexpectedTypeException($constraint, ImmutableVariantAxesValues::class);
        }

        if (null === $entity->getId() || null === $entity->getFamilyVariant()) {
            return;
        }

        $axisCodes = array_map(function (AttributeInterface $axis) {
            return $axis->getCode();
        }, $this->attributesProvider->getAxes($entity));

        if (null === $entity->getRawValues()) {
            return;
        }

        $originalValues = $this->valueCollectionFactory->createFromStorageFormat($entity->getRawValues());

        foreach ($axisCodes as $code) {
            $originalValue = $originalValues->getByCodes($code);
            $newValue = $entity->getValue($code);
            if (null !== $newValue && null !== $originalValue && !$originalValue->isEqual($newValue)) {
                if (is_bool($newValue->getData())) {
                    $newValue = $newValue ? 'true' : 'false';
                }
                $this->context->buildViolation(
                    ImmutableVariantAxesValues::UPDATED_VARIANT_AXIS_VALUE,
                    [
                        '%variant_axis%' => $code,
                        '%provided_value%' => (string) $newValue,
                    ]
                )->atPath($constraint->propertyPath)->addViolation();
            }
        }
    }
}
