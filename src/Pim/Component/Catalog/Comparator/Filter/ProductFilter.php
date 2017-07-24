<?php

namespace Pim\Component\Catalog\Comparator\Filter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter product's values to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFilter implements FilterInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var array */
    protected $productFields;

    /** @var array[] */
    protected $attributeTypeByCodes;

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
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->attributeRepository = $attributeRepository;
        $this->productFields = $productFields;
        $this->attributeTypeByCodes = [];
    }

    /**
     * {@inheritdoc}
     */
    public function filter(EntityWithValuesInterface $product, array $newProduct): array
    {
        $originalValues = $this->getOriginalProduct($product);

        $result = [];
        foreach ($newProduct as $code => $value) {
            if ('values' === $code) {
                $data = $this->compareAttribute($originalValues, $value);
            } elseif (in_array($code, $this->productFields)) {
                $data = $this->compareField($originalValues, $value, $code);
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
    protected function compareField(array $originalValues, $field, $code): ?array
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
     * @param array $originalValues
     * @param array $values
     *
     * @throws \LogicException
     *
     * @return array|null
     */
    protected function compareAttribute(array $originalValues, array $values): ?array
    {
        $this->cacheAttributeTypeByCodes(array_keys($values));

        $result = [];
        foreach ($values as $code => $value) {
            if (!isset($this->attributeTypeByCodes[$code])) {
                throw UnknownPropertyException::unknownProperty($code);
            }

            $comparator = $this->comparatorRegistry->getAttributeComparator($this->attributeTypeByCodes[$code]);

            foreach ($value as $data) {
                $diff = $comparator->compare($data, $this->getOriginalAttribute($originalValues, $data, $code));

                if (null !== $diff) {
                    $result[$code][] = $diff;
                }
            }
        }

        return !empty($result) ? ['values' => $result] : null;
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
    protected function getOriginalAttribute(array $originalValues, array $attribute, $code): array
    {
        $key = $this->buildKey($attribute, $code);

        return !isset($originalValues['values'][$key]) ? [] : $originalValues['values'][$key];
    }

    /**
     * Normalize original product
     *
     * @param EntityWithValuesInterface $product
     *
     * @return array
     */
    protected function getOriginalProduct(EntityWithValuesInterface $product): array
    {
        $originalProduct = $this->normalizer->normalize($product, 'standard');

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
    protected function flatProductValues(array $product): array
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
    protected function mergeValueToResult(array $collection, array $value): array
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
    protected function buildKey(array $data, $code): string
    {
        return sprintf('%s-%s-%s', $code, $data['locale'], $data['scope']);
    }

    /**
     * @param array $codes
     */
    private function cacheAttributeTypeByCodes(array $codes): void
    {
        $codesToFetch = array_diff($codes, array_keys($this->attributeTypeByCodes));

        // we can have numeric keys here, we can't use array_merge :(
        $this->attributeTypeByCodes += $this->attributeRepository->getAttributeTypeByCodes($codesToFetch);
    }
}
