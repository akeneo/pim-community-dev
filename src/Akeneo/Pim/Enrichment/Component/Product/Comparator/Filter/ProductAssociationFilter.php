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
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /**
     * @param NormalizerInterface $normalizer
     * @param ComparatorRegistry  $comparatorRegistry
     */
    public function __construct(NormalizerInterface $normalizer, ComparatorRegistry $comparatorRegistry)
    {
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(EntityWithValuesInterface $product, array $newValues): array
    {
        $originalAssociations = $this->normalizer->normalize($product, 'standard');
        $hasAssociation = $this->hasNewAssociations($newValues);

        if (!$hasAssociation && empty($originalAssociations)) {
            return [];
        }

        $result = [];
        foreach ($newValues[ProductNormalizer::FIELD_ASSOCIATIONS] as $type => $field) {
            foreach ($field as $key => $association) {
                $data = $this->compareAssociation($originalAssociations, $association, $type, $key);

                if (null !== $data) {
                    $result[ProductNormalizer::FIELD_ASSOCIATIONS][$type][$key] = $data;
                }
            }
        }

        return $result;
    }

    /**
     * Has association(s) in new values ?
     *
     * @param array $convertedItem
     *
     * @return bool
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
     * Compare product's association
     *
     * @param array  $originalAssociations original associations
     * @param array  $associations         product's associations
     * @param string $type                 type of association (PACK, SUBSTITUTION, etc)
     * @param string $key                  key of group (products or groups)
     *
     * @throws \LogicException
     *
     * @return array|null
     */
    protected function compareAssociation(array $originalAssociations, array $associations, $type, $key): ?array
    {
        $comparator = $this->comparatorRegistry->getFieldComparator(ProductNormalizer::FIELD_ASSOCIATIONS);
        $diff = $comparator->compare($associations, $this->getOriginalAssociation($originalAssociations, $type, $key));

        if (null !== $diff) {
            return $diff;
        }

        return null;
    }

    /**
     * @param array  $originalAssociations original associations
     * @param string $type                 type of association (PACK, SUBSTITUTION, etc)
     * @param string $key                  key of group (products or groups)
     *
     * @return array
     */
    protected function getOriginalAssociation(array $originalAssociations, $type, $key): array
    {
        return !isset($originalAssociations[$type][$key]) ? [] : $originalAssociations[$type][$key];
    }
}
