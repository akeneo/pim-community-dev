<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Non indexed value collection for reading purpose
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReadValueCollection implements ValueCollectionInterface
{
    /** @var ValueInterface[] */
    private $values;

    /**
     * @param ValueInterface[] $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
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
    public function contains(ValueInterface $value)
    {
        return in_array($value, $this->values, true);
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
    public function filter(\Closure $filterBy)
    {
        $filteredValues = array_filter($this->values, $filterBy);

        return new self($filteredValues);
    }
}
