<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

class FieldConfig implements ConfigInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ValueConfig[]
     */
    protected $values = array();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param ValueConfig[] $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param ValueConfig $value
     */
    public function addValue(ValueConfig $value)
    {
        $this->values[] = $value;
    }

    /**
     * @param  callable            $filter
     * @return array|ValueConfig[]
     */
    public function getValues(\Closure $filter = null)
    {
        return $filter ? array_filter($this->values, $filter) : $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->name,
            $this->values,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->name,
            $this->values,
            ) = unserialize($serialized);
    }
}
