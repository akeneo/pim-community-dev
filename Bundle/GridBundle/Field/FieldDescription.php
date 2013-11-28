<?php

namespace Oro\Bundle\GridBundle\Field;

use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Property\FieldProperty;

class FieldDescription implements FieldDescriptionInterface
{
    /**
     * @var string the field name
     */
    protected $fieldName;

    /**
     * @var string the field name
     */
    protected $name;

    /**
     * @var PropertyInterface
     */
    protected $property;

    /**
     * @var array the option collection
     */
    protected $options = array();

    /**
     * @var string|integer the type
     */
    protected $type;

    /**
     * @var string the template name
     */
    protected $template;

    /**
     * @var array the ORM field information
     */
    protected $fieldMapping;

    /**
     * @var string|integer the original mapping type
     */
    protected $mappingType;

    /**
     * @var array the ORM association mapping
     */
    protected $associationMapping;

    /**
     * {@inheritdoc}
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        if (!$this->getFieldName()) {
            $this->setFieldName(substr(strrchr('.' . $name, '.'), 1));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty()
    {
        if (!$this->property) {
            $this->property = new FieldProperty($this);
        }
        return $this->property;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty(PropertyInterface $property)
    {
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

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
        $options['field_mapping'] = $this->createFieldMapping($options);
        $this->setFieldMapping($options['field_mapping']);

        $this->options = $options;
    }

    /**
     * Creates field mapping options
     *
     * @param array $options
     * @return array
     */
    protected function createFieldMapping(array $options)
    {
        $fieldMapping = array(
            'fieldName' => $this->getFieldName(),
        );
        if (isset($options['entity_alias'])) {
            $fieldMapping['entityAlias'] = $options['entity_alias'];
        }
        if (isset($options['expression'])) {
            $fieldMapping['fieldExpression'] = $options['expression'];
        } elseif (isset($options['entity_alias'])) {
            $fieldMapping['fieldExpression'] = $options['entity_alias'] . '.' . $this->getFieldName();
        }
        if (isset($options['filter_by_where'])) {
            $fieldMapping['filterByWhere'] = $options['filter_by_where'];
        }
        if (isset($options['filter_by_having'])) {
            $fieldMapping['filterByHaving'] = $options['filter_by_having'];
        }
        if (isset($options['field_mapping'])) {
            $fieldMapping = array_merge($fieldMapping, $options['field_mapping']);
        }
        return $fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociationMapping($associationMapping)
    {
        $this->associationMapping = $associationMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMapping()
    {
        return $this->associationMapping;
    }

    /**
     * set the field mapping information
     *
     * @param array $fieldMapping
     *
     * @return void
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * return the field mapping definition
     *
     * @return array the field mapping definition
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntity()
    {
        if ($this->associationMapping) {
            return $this->associationMapping['targetEntity'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier()
    {
        return isset($this->fieldMapping['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeOption($name, array $options = array())
    {
        if (!isset($this->options[$name])) {
            $this->options[$name] = array();
        }

        if (!is_array($this->options[$name])) {
            throw new \RuntimeException(sprintf('The key "%s" does not point to an array value', $name));
        }

        $this->options[$name] = array_merge($this->options[$name], $options);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeOptions(array $options = array())
    {
        $this->setOptions(array_merge_recursive($this->options, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function setMappingType($mappingType)
    {
        $this->mappingType = $mappingType;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable()
    {
        return $this->getOption('sortable', false);
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterable()
    {
        return $this->getOption('filterable', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortFieldMapping()
    {
        return $this->getOption('sort_field_mapping', $this->getFieldMapping());
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParentAssociationMapping()
    {
        return $this->getOption('sort_parent_association_mappings', array());
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable()
    {
        return $this->getOption('editable', false);
    }

    /**
     * @return boolean
     */
    public function isShown()
    {
        return $this->getOption('show_column', true);
    }
}
