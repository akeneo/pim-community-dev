<?php

namespace Oro\Bundle\GridBundle\Builder;

use Oro\Bundle\GridBundle\Builder\ListBuilderInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class ListBuilder implements ListBuilderInterface
{
    /**
     * @param array $options
     * @return FieldDescriptionCollection
     */
    public function getBaseList(array $options = array())
    {
        return new FieldDescriptionCollection();
    }

    /**
     * Modify a field description to display it in the list view.
     *
     * @param null|mixed $type
     * @param FieldDescriptionInterface $fieldDescription
     */
    public function buildField($type = null, FieldDescriptionInterface $fieldDescription = null)
    {
        $fieldDescription->setType($type);
    }

    /**
     * Modify a field description and add it to the displayed columns.
     *
     * @param FieldDescriptionCollection $list
     * @param null|mixed $type
     * @param FieldDescriptionInterface $fieldDescription
     */
    public function addField(
        FieldDescriptionCollection $list,
        $type = null,
        FieldDescriptionInterface $fieldDescription = null
    ) {
        $this->buildField($type, $fieldDescription);
        $list->add($fieldDescription);
    }
}
