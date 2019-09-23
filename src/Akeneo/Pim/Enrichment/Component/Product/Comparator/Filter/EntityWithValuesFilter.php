<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter entitiy's values to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityWithValuesFilter implements FilterInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var array */
    protected $entityFields;

    /** @var FilterInterface */
    protected $productFieldFilter;

    /** @var array[] */
    protected $attributeTypeByCodes;

    /**
     * @param NormalizerInterface          $normalizer
     * @param ComparatorRegistry           $comparatorRegistry
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FilterInterface              $productFieldFilter
     * @param array                        $entityFields
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository,
        FilterInterface $productFieldFilter,
        array $entityFields
    ) {
        $this->normalizer = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->attributeRepository = $attributeRepository;
        $this->entityFields = $entityFields;
        $this->productFieldFilter = $productFieldFilter;
        $this->attributeTypeByCodes = [];
    }

    /**
     * {@inheritdoc}
     */
    public function filter(EntityWithValuesInterface $entity, array $newEntity): array
    {
        $originalValues = $this->getOriginalEntity($entity);
        $result = [];
        $fields = [];

        foreach ($newEntity as $code => $value) {
            if ('values' === $code) {
                $data = $this->compareAttribute($originalValues, $value);

                if (null !== $data) {
                    $result = $this->mergeValueToResult($result, $data);
                }
            } elseif (in_array($code, $this->entityFields)) {
                $fields[$code] = $value;
            } else {
                throw new \LogicException(sprintf('Cannot filter value of field "%s"', $code));
            }
        }

        $productFieldsFilter = $this->productFieldFilter->filter($entity, $fields);
        $result = $this->mergeValueToResult($result, $productFieldsFilter);

        return $result;
    }

    /**
     * Compare entity's values
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
                if (!is_array($data)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $code,
                        'one of the values is not an array',
                        static::class,
                        $values
                    );
                }

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
     * Normalize original entity
     *
     * @param EntityWithValuesInterface $entity
     *
     * @return array
     */
    protected function getOriginalEntity(EntityWithValuesInterface $entity): array
    {
        $originalEntity = $this->normalizer->normalize($entity, 'standard');

        return $this->flatEntityValues($originalEntity);
    }

    /**
     * Flat entity values to have keys formatted like that: $code-$locale-$scope.
     * That simplifies the search when we compare two arrays
     *
     * @param array $entity
     *
     * @return array
     */
    protected function flatEntityValues(array $entity): array
    {
        if (isset($entity['values'])) {
            $values = $entity['values'];
            unset($entity['values']);

            foreach ($values as $code => $value) {
                foreach ($value as $data) {
                    $entity['values'][$this->buildKey($data, $code)] = $data;
                }
            }
        }

        return $entity;
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
