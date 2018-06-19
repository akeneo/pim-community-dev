<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Builder;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use PimEnterprise\Component\Workflow\Builder\EntityWithValuesDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Factory\EntityWithValuesDraftFactory;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Draft builder to have modifications on entity with values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class EntityWithValuesDraftBuilder implements EntityWithValuesDraftBuilderInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var EntityWithValuesDraftFactory */
    protected $factory;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $entityWithValuesDraftRepository;

    /** @var ValueCollectionFactoryInterface */
    protected $valueCollectionFactory;

    /** @var ValueFactory */
    protected $valueFactory;

    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesDraftFactory $factory,
        EntityWithValuesDraftRepositoryInterface $entityWithValuesDraftRepository,
        ValueCollectionFactoryInterface $valueCollectionFactory,
        ValueFactory $valueFactory
    ) {
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->attributeRepository = $attributeRepository;
        $this->factory = $factory;
        $this->entityWithValuesDraftRepository = $entityWithValuesDraftRepository;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(EntityWithValuesInterface $entityWithValues, string $username): ?EntityWithValuesDraftInterface
    {
        $values = $entityWithValues instanceof EntityWithFamilyVariantInterface ?
            $entityWithValues->getValuesForVariation() : $entityWithValues->getValues();

        $newValues = $this->normalizer->normalize($values, 'standard');
        $originalValues = $this->getOriginalValues($entityWithValues);

        $values = [];
        foreach ($newValues as $code => $newValue) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);

            if (null === $attribute) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s".', $code));
            }

            foreach ($newValue as $index => $changes) {
                $comparator = $this->comparatorRegistry->getAttributeComparator($attribute->getType());
                $diffAttribute = $comparator->compare(
                    $changes,
                    $this->getOriginalValue($originalValues, $code, $changes['locale'], $changes['scope'])
                );

                if (null !== $diffAttribute) {
                    $diff['values'][$code][] = $diffAttribute;

                    $attribute = $this->attributeRepository->findOneByIdentifier($code);
                    $values[] = $this->valueFactory->create(
                        $attribute,
                        $changes['scope'],
                        $changes['locale'],
                        $changes['data']
                    );
                }
            }
        }

        if (!empty($diff)) {
            $entityWithValuesDraft = $this->getEntityWithValuesDraft($entityWithValues, $username);
            $entityWithValuesDraft->setValues(new ValueCollection($values));
            $entityWithValuesDraft->setChanges($diff);
            $entityWithValuesDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT);

            return $entityWithValuesDraft;
        }

        return null;
    }

    protected function getEntityWithValuesDraft(EntityWithValuesInterface $entityWithValues, string $username): EntityWithValuesDraftInterface
    {
        if (null === $entityWithValuesDraft = $this->entityWithValuesDraftRepository->findUserEntityWithValuesDraft(
                $entityWithValues,
                $username
            )) {
            $entityWithValuesDraft = $this->factory->createEntityWithValueDraft($entityWithValues, $username);
        }

        return $entityWithValuesDraft;
    }

    protected function getOriginalValues(EntityWithValuesInterface $entityWithValues): array
    {
        $rawValues = $entityWithValues->getRawValues();
        $originalValues = $this->valueCollectionFactory->createFromStorageFormat($rawValues);

        return $this->normalizer->normalize($originalValues, 'standard');
    }

    protected function getOriginalValue(array $originalValues, string $code, ?string $locale, ?string $channel): array
    {
        if (!isset($originalValues[$code])) {
            return [];
        }

        foreach ($originalValues[$code] as $originalValue) {
            if ($originalValue['locale'] === $locale && $originalValue['scope'] === $channel) {
                return $originalValue;
            }
        }

        return [];
    }
}
