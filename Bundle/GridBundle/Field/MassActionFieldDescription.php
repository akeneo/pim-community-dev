<?php

namespace Oro\Bundle\GridBundle\Field;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class MassActionFieldDescription extends FieldDescription
{
    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        // set the type if provided
        if (isset($options['type'])) {
            $this->setType($options['type']);
            unset($options['type']);
        }

        // set the field_name if provided
        if (isset($options['field_name'])) {
            $this->setFieldName($options['field_name']);
        } else {
            $options['field_name'] = $this->getFieldName();
        }

        // remove property value
        if (isset($options['template'])) {
            $this->setTemplate($options['template']);
            unset($options['template']);
        }

        // set field_mapping
        if (isset($options['field_mapping'])) {
            $this->setFieldMapping($options['field_mapping']);
        } else {
            $fieldMapping = array(
                'fieldName' => $this->getFieldName(),
            );
            if (isset($options['entity_alias'])) {
                $fieldMapping['entityAlias'] = $options['entity_alias'];
            }
            if (isset($options['expression'])) {
                $fieldMapping['fieldExpression'] = $options['expression'];
            }
            if (isset($options['filter_by_where'])) {
                $fieldMapping['filterByWhere'] = $options['filter_by_where'];
            }
            if (isset($options['filter_by_having'])) {
                $fieldMapping['filterByHaving'] = $options['filter_by_having'];
            }
            $this->setFieldMapping($fieldMapping);
            $options['field_mapping'] = $fieldMapping;
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntity()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortFieldMapping()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParentAssociationMapping()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable()
    {
        return false;
    }
}
