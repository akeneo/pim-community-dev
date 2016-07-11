<?php

namespace Pim\Component\Catalog\Comparator\Filter;

use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter product's association to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationFilter implements ProductFilterInterface
{
    /** @staticvar string */
    const ASSOCIATIONS_FIELD = 'associations';

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
    public function filter(ProductInterface $product, array $newValues)
    {
        $originalAssociations = $this->getOriginalAssociations($product);
        $hasAssociation = $this->hasNewAssociations($newValues);

        if (!$hasAssociation && empty($originalAssociations)) {
            return [];
        }

        $result = [];
        foreach ($newValues[self::ASSOCIATIONS_FIELD] as $type => $field) {
            foreach ($field as $key => $association) {
                $data = $this->compareAssociation($originalAssociations, $association, $type, $key);

                if (null !== $data) {
                    $result[self::ASSOCIATIONS_FIELD][$type][$key] = $data;
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
    protected function hasNewAssociations(array $convertedItem)
    {
        if (!isset($convertedItem['associations'])) {
            return false;
        }

        foreach ($convertedItem['associations'] as $association) {
            if (!empty($association['products']) || !empty($association['groups'])) {
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
    protected function compareAssociation(array $originalAssociations, array $associations, $type, $key)
    {
        $comparator = $this->comparatorRegistry->getFieldComparator(self::ASSOCIATIONS_FIELD);
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
    protected function getOriginalAssociation(array $originalAssociations, $type, $key)
    {
        return !isset($originalAssociations[$type][$key]) ? [] : $originalAssociations[$type][$key];
    }

    /**
     * Normalize original associations
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOriginalAssociations(ProductInterface $product)
    {
        $originalProduct = $this->normalizer->normalize($product, 'json', ['only_associations' => true]);

        return isset($originalProduct[self::ASSOCIATIONS_FIELD]) ? $originalProduct[self::ASSOCIATIONS_FIELD] : [];
    }
}
