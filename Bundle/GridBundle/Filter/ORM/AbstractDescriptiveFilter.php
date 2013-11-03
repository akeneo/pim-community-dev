<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class AbstractDescriptiveFilter implements FilterInterface
{
    const CONDITION_OR  = 'OR';
    const CONDITION_AND = 'AND';

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var string
     */
    protected $condition;

    /**
     * {@inheritdoc}
     */
    public function getFieldOptions()
    {
        return $this->getOption('field_options', array());
    }

    /**
     * {@inheritdoc}
     */
    public function isNullable()
    {
        return $this->getOption('nullable', true);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
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
    public function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
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
    public function getFieldType()
    {
        return $this->getOption('field_type', 'text');
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
    public function setLabel($label)
    {
        $this->setOption('label', $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName()
    {
        $fieldName = $this->getOption('field_name');

        if (!$fieldName) {
            throw new \RunTimeException(
                sprintf('The option `field_name` must be set for field : `%s`', $this->getName())
            );
        }

        return $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentAssociationMappings()
    {
        return $this->getOption('parent_association_mappings', array());
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMapping()
    {
        $fieldMapping = $this->getOption('field_mapping');

        if (!$fieldMapping) {
            throw new \RunTimeException(
                sprintf('The option `field_mapping` must be set for field : `%s`', $this->getName())
            );
        }

        return $fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMapping()
    {
        $associationMapping = $this->getOption('association_mapping');

        if (!$associationMapping) {
            throw new \RunTimeException(
                sprintf('The option `association_mapping` must be set for field : `%s`', $this->getName())
            );
        }

        return $associationMapping;
    }

    /**
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $condition
     *
     * @return void
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
