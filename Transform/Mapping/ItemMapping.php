<?php

namespace Akeneo\Bundle\BatchBundle\Transform\Mapping;

/**
 * Item Mapping
 *
 *
 */
class ItemMapping
{
    /**
     * List of fields
     * @var multitype:FieldMapping
     */
    protected $fields = array();

    /**
     * Add a field to list
     * @param string  $source       source field name
     * @param string  $destination  destination field name
     * @param boolean $isIdentifier define is field is an identifier
     *
     * @return \Akeneo\Bundle\BatchBundle\Model\Mapping\ItemMapping
     */
    public function add($source, $destination, $isIdentifier = false)
    {
        $field = new FieldMapping();
        $field->setSource($source);
        $field->setDestination($destination);
        $field->setIdentifier($isIdentifier);

        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove a field from list
     * @param FieldMapping $field
     *
     * @return \Akeneo\Bundle\BatchBundle\Model\Mapping\ItemMapping
     */
    public function remove(FieldMapping $field)
    {
        if (isset($fields[$field])) {
            unset($fields[$field]);
        }

        return $this;
    }

    /**
     * Get fields list
     *
     * @return multitype:\Akeneo\Bundle\BatchBundle\Mapping\FieldMapping
     */
    public function getFields()
    {
        return $this->fields;
    }
}
