<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
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
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    /**
     * @param EntityManagerInterface                    $entityManager
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param ValueCollectionFactoryInterface           $valueCollectionFactory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ValueCollectionFactoryInterface $valueCollectionFactory
    ) {
        $this->entityManager = $entityManager;
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

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($entity);

        $originalValues = $this->valueCollectionFactory->createFromStorageFormat($originalData['rawValues']);

        foreach ($axisCodes as $code) {
            $originalValue = $originalValues->getByCodes($code);
            $newValue = $entity->getValue($code);
            if (null !== $originalValue && !$originalValue->isEqual($newValue)) {
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
