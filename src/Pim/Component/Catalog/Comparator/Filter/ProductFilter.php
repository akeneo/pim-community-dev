<?php

namespace Pim\Component\Catalog\Comparator\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter product's values to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFilter implements ProductFilterInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var array */
    protected $productFields;

    /**
     * @param NormalizerInterface          $normalizer
     * @param ComparatorRegistry           $comparatorRegistry
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array                        $productFields
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository,
        array $productFields
    ) {
        $this->normalizer          = $normalizer;
        $this->comparatorRegistry  = $comparatorRegistry;
        $this->attributeRepository = $attributeRepository;
        $this->productFields       = $productFields;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(ProductInterface $product, array $newValues)
    {
        $originalValues = $this->getOriginalProduct($product);
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($newValues));

        $result = [];
        foreach ($newValues as $code => $value) {
            if (in_array($code, $this->productFields)) {
                $data = $this->compareField($originalValues, $value, $code);
            } elseif (isset($attributeTypes[$code])) {
                $data = $this->compareAttribute($originalValues, $value, $attributeTypes, $code);
            } else {
                throw new \LogicException(sprintf('Cannot filter value of field "%s"', $code));
            }

            if (null !== $data) {
                $result = $this->mergeValueToResult($result, $data);
            }
        }

        return $result;
    }

    /**
     * Compare product's field
     *
     * @param array  $originalValues
     * @param mixed  $field
     * @param string $code
     *
     * @throws \LogicException
     *
     * @return array|null
     */
    protected function compareField(array $originalValues, $field, $code)
    {
        $comparator = $this->comparatorRegistry->getFieldComparator($code);
        $diff = $comparator->compare($field, $this->getOriginalField($originalValues, $code));

        if (null !== $diff) {
            return [$code => $diff];
        }

        return null;
    }

    /**
     * Compare product's values
     *
     * @param array  $originalValues
     * @param array  $attribute
     * @param array  $attributeTypes
     * @param string $code
     *
     * @throws \LogicException
     *
     * @return array|null
     */
    protected function compareAttribute(array $originalValues, array $attribute, array $attributeTypes, $code)
    {
        $comparator = $this->comparatorRegistry->getAttributeComparator($attributeTypes[$code]);
        $result = [];

        foreach ($attribute as $data) {
            $diff = $comparator->compare($data, $this->getOriginalAttribute($originalValues, $data, $code));

            if (null !== $diff) {
                $result[$code][] = $diff;
            }
        }

        return !empty($result) ? $result : null;
    }

    /**
     * @param array  $originalValues
     * @param string $code
     *
     * @return array|string|null
     */
    protected function getOriginalField(array $originalValues, $code)
    {
        return !isset($originalValues[$code]) ? null : $originalValues[$code];
    }

    /**
     * @param array  $originalValues
     * @param array  $attribute
     * @param string $code
     *
     * @return array
     */
    protected function getOriginalAttribute(array $originalValues, array $attribute, $code)
    {
        $key = $this->buildKey($attribute, $code);

        return !isset($originalValues['values'][$key]) ? [] : $originalValues['values'][$key];
    }

    /**
     * Normalize original product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOriginalProduct(ProductInterface $product)
    {
        $originalProduct = $this->normalizer->normalize($product, 'json', ['exclude_associations' => true]);

        return $this->flatProductValues($originalProduct);
    }

    /**
     * Flat product values to have keys formatted like that: $code-$locale-$scope.
     * That simplifies the search when we compare two arrays
     *
     * @param array $product
     *
     * @return array
     */
    protected function flatProductValues(array $product)
    {
        if (isset($product['values'])) {
            $values = $product['values'];
            unset($product['values']);

            foreach ($values as $code => $value) {
                foreach ($value as $data) {
                    $product['values'][$this->buildKey($data, $code)] = $data;
                }
            }
        }

        return $product;
    }

    /**
     * @param array $collection The collection in which we add the element
     * @param array $value      The structured value to add to the collection
     *
     * @return array
     */
    protected function mergeValueToResult(array $collection, array $value)
    {
        foreach ($value as $code => $data) {
            if (array_key_exists($code, $collection)) {
                $collection[$code] = array_merge_recursive($collection[$code], $data);
            } else {
                $collection[$code] = $data;
            }
        }

        return $collection;
    }

    /**
     * @param array  $data
     * @param string $code
     *
     * @return string
     */
    protected function buildKey(array $data, $code)
    {
        return sprintf('%s-%s-%s', $code, $data['locale'], $data['scope']);
    }
}
