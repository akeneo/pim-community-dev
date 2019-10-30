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
class WriteValueCollection implements \Countable, \IteratorAggregate
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
     * @param WriteValueCollection $collection
     *
     * @return WriteValueCollection
     */
    public static function fromCollection(WriteValueCollection $collection): WriteValueCollection
    {
        return new static($collection->toArray());
    }

    public static function fromReadValueCollection(ReadValueCollection $valueCollection): self
    {
        return new self($valueCollection->toArray());
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
        if (!array_key_exists($key, $this->values)) {
            return null;
        }

        $removed = $this->values[$key];
        $attributeCode = $removed->getAttributeCode();
        unset($this->values[$key]);

        $this->valuesNumberPerAttribute[$attributeCode]--;
        if (0 === $this->valuesNumberPerAttribute[$attributeCode]) {
            unset($this->attributeCodes[$attributeCode]);
            unset($this->valuesNumberPerAttribute[$attributeCode]);
        }

        return $removed;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(ValueInterface $value)
    {
        $key = array_search($value, $this->values, true);

        if (false === $key) {
            return false;
        }

        $this->removeKey($key);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function removeByAttributeCode(string $attributeCode)
    {
        $removed = false;
        foreach ($this->values as $value) {
            if ($attributeCode === $value->getAttributeCode()) {
                $this->remove($value);
                $removed = true;
            }
        }

        return $removed;
    }

    /**
     * {@inheritDoc}
     */
    public function containsKey($key)
    {
        return array_key_exists($key, $this->values);
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
        $key = $this->generateKey($value->getAttributeCode(), $value->getScopeCode(), $value->getLocaleCode());

        return $this->getByKey($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getByKey($key)
    {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getByCodes($attributeCode, $channelCode = null, $localeCode = null)
    {
        $key = $this->generateKey($attributeCode, $channelCode, $localeCode);

        return $this->getByKey($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getKeys()
    {
        return array_keys($this->values);
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

        $key = $this->generateKey($attributeCode, $value->getScopeCode(), $value->getLocaleCode());

        if (isset($this->values[$key])) {
            return false;
        }

        $this->values[$key] = $value;

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

    private function generateKey(string $attributeCode, ?string $channelCode, ?string $localeCode): string
    {
        $channelCode = null !== $channelCode ? $channelCode : '<all_channels>';
        $localeCode = null !== $localeCode ? $localeCode : '<all_locales>';
        $key = sprintf('%s-%s-%s', $attributeCode, $channelCode, $localeCode);

        return $key;
    }
}
