<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter product's association to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationFilter implements FilterInterface
{
    /** @var NormalizerInterface */
    protected $associationsNormalizer;

    /** @var NormalizerInterface */
    protected $quantifiedAssociationsNormalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    public function __construct(
        NormalizerInterface $associationsNormalizer,
        NormalizerInterface $quantifiedAssociationsNormalizer,
        ComparatorRegistry $comparatorRegistry
    ) {
        $this->associationsNormalizer = $associationsNormalizer;
        $this->quantifiedAssociationsNormalizer = $quantifiedAssociationsNormalizer;
        $this->comparatorRegistry = $comparatorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(EntityWithValuesInterface $product, array $newValues): array
    {
        $originalAssociations = $this->associationsNormalizer->normalize($product, 'standard');
        $originalQuantifiedAssociations = $this->quantifiedAssociationsNormalizer->normalize($product, 'standard');

        if (
            !$this->hasNewAssociations($newValues) &&
            !$this->hasNewQuantifiedAssociations($newValues) &&
            empty($originalAssociations) &&
            empty($originalQuantifiedAssociations)
        ) {
            return [];
        }

        $result = [];

        if (!isset($newValues[ProductNormalizer::FIELD_ASSOCIATIONS])) {
            return $result;
        }

        foreach ($newValues[ProductNormalizer::FIELD_ASSOCIATIONS] as $type => $field) {
            foreach ($field as $key => $association) {
                $data = $this->compareAssociations($originalAssociations, $association, $type, $key);

                if (null !== $data) {
                    $result[ProductNormalizer::FIELD_ASSOCIATIONS][$type][$key] = $data;
                }
            }
        }

        if (!isset($newValues[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS])) {
            return $result;
        }

        foreach ($newValues[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS] as $type => $field) {
            foreach ($field as $key => $association) {
                $data = $this->compareQuantifiedAssociations($originalQuantifiedAssociations, $association, $type, $key);

                if (null !== $data) {
                    $result[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS][$type][$key] = $data;
                }
            }
        }

        return $result;
    }

    /**
     * Has association(s) in new values?
     */
    protected function hasNewAssociations(array $convertedItem): bool
    {
        if (!isset($convertedItem['associations'])) {
            return false;
        }

        foreach ($convertedItem['associations'] as $association) {
            if (!empty($association['products']) || !empty($association['groups']) || !empty($association['product_models'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has quantified association(s) in new values?
     */
    protected function hasNewQuantifiedAssociations(array $convertedItem): bool
    {
        if (!isset($convertedItem['quantified_associations'])) {
            return false;
        }

        foreach ($convertedItem['quantified_associations'] as $association) {
            if (!empty($association['products']) || !empty($association['product_models'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare product's associations
     *
     * @throws \LogicException
     */
    protected function compareAssociations(array $originalAssociations, array $newAssociations, string $type, string $key): ?array
    {
        $comparator = $this->comparatorRegistry->getFieldComparator(ProductNormalizer::FIELD_ASSOCIATIONS);

        return $comparator->compare($newAssociations, $this->getOriginalAssociation($originalAssociations, $type, $key));
    }

    /**
     * Compare product's quantified associations
     *
     * @throws \LogicException
     */
    protected function compareQuantifiedAssociations(array $originalQuantifiedAssociations, array $newQuantifiedAssociations, string $type, string $key): ?array
    {
        $comparator = $this->comparatorRegistry->getFieldComparator(ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS);

        return $comparator->compare($newQuantifiedAssociations, $this->getOriginalAssociation($originalQuantifiedAssociations, $type, $key));
    }

    protected function getOriginalAssociation(array $originalAssociations, string $type, string $key): array
    {
        return !isset($originalAssociations[$type][$key]) ? [] : $originalAssociations[$type][$key];
    }
}
