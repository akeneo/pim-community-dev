<?php
namespace Pim\Bundle\BatchBundle\Transform\Mapping;

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
     * @return \Pim\Bundle\BatchBundle\Model\Mapping\ItemMapping
     */
    public function add($source, $destination, $isIdentifier = false)
    {
        $field = new FieldMapping();
        $field->setSource($source);
        $field->setDestination($destination);
        $field->setIsIdentifier($isIdentifier);

        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove a field from list
     * @param FieldMapping $field
     *
     * @return \Pim\Bundle\BatchBundle\Model\Mapping\ItemMapping
     */
    public function remove(FieldMapping $field)
    {
        // TODO ?
        return $this;
    }

    /**
     * Get fields list
     *
     * @return multitype:\Pim\Bundle\BatchBundle\Mapping\FieldMapping
     */
    public function getFields()
    {
        return $this->fields;
    }
}
