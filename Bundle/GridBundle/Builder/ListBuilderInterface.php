<?php

namespace Oro\Bundle\GridBundle\Builder;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

interface ListBuilderInterface
{
    /**
     * @param array $options
     * @return FieldDescriptionCollection
     */
    public function getBaseList(array $options = array());

    /**
     * Modify a field description to display it in the list view.
     *
     * @param null|mixed $type
     * @param FieldDescriptionInterface $fieldDescription
     */
    public function buildField($type = null, FieldDescriptionInterface $fieldDescription = null);

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
    );
}
