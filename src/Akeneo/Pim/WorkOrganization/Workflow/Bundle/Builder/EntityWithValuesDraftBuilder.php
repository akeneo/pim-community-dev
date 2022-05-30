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
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\EntityWithValuesDraftFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
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

    /** @var GetAttributes */
    protected $getAttributes;

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
        GetAttributes $getAttributes,
        EntityWithValuesDraftFactory $factory,
        EntityWithValuesDraftRepositoryInterface $entityWithValuesDraftRepository,
        WriteValueCollectionFactory $valueCollectionFactory,
        ValueFactory $valueFactory
    ) {
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->getAttributes = $getAttributes;
        $this->factory = $factory;
        $this->entityWithValuesDraftRepository = $entityWithValuesDraftRepository;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(EntityWithValuesInterface $entityWithValues, DraftSource $draftSource): ?EntityWithValuesDraftInterface
    {
        $values = $entityWithValues instanceof EntityWithFamilyVariantInterface ?
            $entityWithValues->getValuesForVariation() : $entityWithValues->getValues();

        $newValues = $this->normalizer->normalize($values, 'standard');
        $originalValues = $this->getOriginalValues($entityWithValues);

        $newValuesWithNullData = $this->fillNewValuesWithNullData($originalValues, $newValues);

        $values = [];
        foreach ($newValuesWithNullData as $code => $newValue) {
            $attribute = $this->getAttributes->forCode((string) $code);

            if (null === $attribute) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s".', $code));
            }

            foreach ($newValue as $index => $changes) {
                $comparator = $this->comparatorRegistry->getAttributeComparator($attribute->type());
                $diffAttribute = $comparator->compare(
                    $changes,
                    $this->getOriginalValue($originalValues, (string) $code, $changes['locale'], $changes['scope'])
                );

                if (null !== $diffAttribute) {
                    $diff['values'][$code][] = $diffAttribute;

                    if (null !== $changes['data']) {
                        $values[] = $this->valueFactory->createByCheckingData(
                            $attribute,
                            $changes['scope'],
                            $changes['locale'],
                            $changes['data']
                        );
                    }
                }
            }
        }

        if (!empty($diff)) {
            $entityWithValuesDraft = $this->getEntityWithValuesDraft($entityWithValues, $draftSource);
            $entityWithValuesDraft->setValues(new WriteValueCollection($values));
            $entityWithValuesDraft->setChanges($diff);
            $entityWithValuesDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT);

            return $entityWithValuesDraft;
        }

        return null;
    }

    protected function getEntityWithValuesDraft(EntityWithValuesInterface $entityWithValues, DraftSource $draftSource): EntityWithValuesDraftInterface
    {
        if (null === $entityWithValuesDraft = $this->entityWithValuesDraftRepository->findUserEntityWithValuesDraft(
                $entityWithValues,
                $draftSource->getAuthor()
            )) {
            $entityWithValuesDraft = $this->factory->createEntityWithValueDraft($entityWithValues, $draftSource);
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

    /**
     * This method will add to the new values set the values deleted during the product update.
     * For example, with this configuration (without scope and locale options, for readability):
     * - original values = ['attr1' => 'attr1', 'attr2' => 'value2']
     * - new values = ['attr2' => 'value2', 'attr3' => 'attr3']
     * This method will add ['attr1' => null] to the new values, to be able to generate the diff.
     *
     * @warning These values will not be stored in the database, it's just to compute the diff.
     *
     * @param array $originalValues
     * @param array $newValues
     *
     * @return array
     */
    private function fillNewValuesWithNullData(array $originalValues, array $newValues)
    {
        foreach ($originalValues as $originalAttributeCode => $originalValueSet) {
            foreach ($originalValueSet as $originalIndex => $originalValue) {
                $foundValueDeleted = false;
                if (isset($newValues[$originalAttributeCode])) {
                    foreach ($newValues[$originalAttributeCode] as $newIndex => $newValue) {
                        $foundValueDeleted = $foundValueDeleted || (
                            $newValue['locale'] === $originalValue['locale'] &&
                            $newValue['scope'] === $originalValue['scope']
                        );
                    }
                }
                if (!$foundValueDeleted) {
                    $newValues[$originalAttributeCode][$originalIndex]['locale'] = $originalValue['locale'];
                    $newValues[$originalAttributeCode][$originalIndex]['scope'] = $originalValue['scope'];
                    $newValues[$originalAttributeCode][$originalIndex]['data'] = null;
                }
            }
        }

        return $newValues;
    }
}
