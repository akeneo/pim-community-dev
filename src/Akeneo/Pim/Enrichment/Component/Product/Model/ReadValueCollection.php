<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Business collection to handle product values.
 *
 * The collection is indexed internally by attribute-channel-locale. The index could be for instance:
 *      description-ecommerce-en_US     for a localizable and scopable attribute
 *      name-<all_channels>-en_US       for a localizable attribute
 *      price-ecommerce-<all_locales>   for a scopable attribute
 *
 * This collection also contains the list of attribute codes used in the collection. This list is indexed by
 * the attribute codes for fast access.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReadValueCollection implements ValueCollectionInterface
{
    /** @var ValueInterface[] */
    private $values;

    /** @var string[] */
    private $attributeCodes;

    /** @var int[] */
    private $valuesNumberPerAttribute;

    /**
     * @param ValueInterface[] $values
     */
    public function __construct(array $values = [])
    {
        $this->values = [];
        $this->attributeCodes = [];
        $this->valuesNumberPerAttribute = [];

        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * @param ValueCollectionInterface $collection
     *
     * @return ValueCollectionInterface
     */
    public static function fromCollection(ValueCollectionInterface $collection): ValueCollectionInterface
    {
        return new static($collection->toArray());
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        return reset($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function last()
    {
        return end($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        return next($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return current($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function removeKey($key)
    {
        throw new \RuntimeException("NOT IMPLEMENTED");
    }

    /**
     * {@inheritDoc}
     */
    public function remove(ValueInterface $value)
    {
        throw new \RuntimeException("NOT IMPLEMENTED");
    }

    /**
     * {@inheritDoc}
     */
    public function removeByAttributeCode(string $attributeCode)
    {
        throw new \RuntimeException("NOT IMPLEMENTED");
    }

    /**
     * {@inheritDoc}
     */
    public function containsKey($key)
    {
        throw new \RuntimeException("NOT IMPLEMENTED");
    }

    /**
     * {@inheritDoc}
     */
    public function contains(ValueInterface $value)
    {
        return in_array($value, $this->values, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getSame(ValueInterface $value)
    {
        throw new \RuntimeException("NOT IMPLEMENTED");
    }

    /**
     * {@inheritDoc}
     */
    public function getByKey($key)
    {
        throw new \RuntimeException("NOT IMPLEMENTED");
    }

    /**
     * {@inheritDoc}
     */
    public function getByCodes($attributeCode, $channelCode = null, $localeCode = null)
    {
        throw new \RuntimeException("NOT IMPLEMENTED");

    }

    /**
     * {@inheritDoc}
     */
    public function getKeys()
    {
        throw new \RuntimeException("NOT IMPLEMENTED");
    }

    /**
     * {@inheritDoc}
     */
    public function getValues()
    {
        return array_values($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function add(ValueInterface $value)
    {
        $attributeCode = $value->getAttributeCode();

        $this->values[] = $value;

        $this->attributeCodes[$attributeCode] = $attributeCode;

        $valuesNumber = $this->valuesNumberPerAttribute[$attributeCode] ?? 0;
        $this->valuesNumberPerAttribute[$attributeCode] = $valuesNumber + 1;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return empty($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->values = [];
        $this->attributeCodes = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeCodes()
    {
        return array_values($this->attributeCodes);
    }

    /**
     * {@inheritDoc}
     */
    public function filter(\Closure $filterBy)
    {
        $filteredValues = array_filter($this->values, $filterBy);

        return new self($filteredValues);
    }
}
