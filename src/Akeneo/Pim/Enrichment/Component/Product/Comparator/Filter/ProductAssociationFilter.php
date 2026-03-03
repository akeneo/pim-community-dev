<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer;
use Ramsey\Uuid\UuidInterface;
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
    public function __construct(
        private NormalizerInterface $associationsNormalizer,
        private NormalizerInterface $quantifiedAssociationsNormalizer,
        private ComparatorRegistry $comparatorRegistry,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * The $newValues contains an array like this
     * [
     *     ProductNormalizer::FIELD_ASSOCIATIONS => [
     *         'XSELL' => [
     *             'products' => ['an_identifier'], ...
     *         ],
     *     ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS => [ ... ]
     * ]
     * Or
     * [
     *     ProductNormalizer::FIELD_ASSOCIATIONS => [
     *         'XSELL' => [
     *             'product_uuids' => ['060309a1-8c7b-4cf3-9cd2-9acf545ff646'], ...
     *         ],
     *     ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS => [ ... ]
     * ]
     *
     * The filtered values should contain products or product_uuids. We normalize the original product associations
     * with products or product_uuids to filter items.
     */
    public function filter(EntityWithValuesInterface $product, array $newValues): array
    {
        $isImportingByUuids = null;
        if (isset($newValues[ProductNormalizer::FIELD_ASSOCIATIONS])) {
            foreach ($newValues[ProductNormalizer::FIELD_ASSOCIATIONS] as $associationsByCode) {
                if (\array_key_exists('products', $associationsByCode)) {
                    if (true === $isImportingByUuids) {
                        throw new \LogicException('You can not filter by uuid and by identifiers');
                    }
                    $isImportingByUuids = false;
                }
                if (\array_key_exists('product_uuids', $associationsByCode)) {
                    if (false === $isImportingByUuids) {
                        throw new \LogicException('You can not filter by uuid and by identifiers');
                    }
                    $isImportingByUuids = true;
                }
            }
        }
        if (null === $isImportingByUuids) {
            $isImportingByUuids = false;
        }

        $originalAssociations = $this->associationsNormalizer->normalize(
            $product,
            'standard',
            ['with_association_uuids' => $isImportingByUuids]
        );
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
        if (!isset($convertedItem[ProductNormalizer::FIELD_ASSOCIATIONS])) {
            return false;
        }

        foreach ($convertedItem[ProductNormalizer::FIELD_ASSOCIATIONS] as $association) {
            if (!empty($association['products']) || !empty($association['groups']) || !empty($association['product_models']) || !empty($association['product_uuids'])) {
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
        if (!isset($convertedItem[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS])) {
            return false;
        }

        foreach ($convertedItem[ProductNormalizer::FIELD_QUANTIFIED_ASSOCIATIONS] as $association) {
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
