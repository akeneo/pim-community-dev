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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\EntityWithValuesDraftFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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

    /** @var WriteValueCollectionFactory */
    protected $valueCollectionFactory;

    /** @var ValueFactory */
    protected $valueFactory;

    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesDraftFactory $factory,
        EntityWithValuesDraftRepositoryInterface $entityWithValuesDraftRepository,
        WriteValueCollectionFactory $valueCollectionFactory,
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
            /** @var AttributeInterface $attribute */
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
                    $attribute = new Attribute(
                        $attribute->getCode(),
                        $attribute->getType(),
                        $attribute->getProperties(),
                        $attribute->isLocalizable(),
                        $attribute->isScopable(),
                        $attribute->getMetricFamily() === '' ? null : $attribute->getMetricFamily(),
                        $attribute->isDecimalsAllowed()
                    );

                    $values[] = $this->valueFactory->createByCheckingData(
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
            $entityWithValuesDraft->setValues(new WriteValueCollection($values));
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
