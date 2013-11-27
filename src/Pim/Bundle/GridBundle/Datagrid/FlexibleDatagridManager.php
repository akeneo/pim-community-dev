<?php

namespace Pim\Bundle\GridBundle\Datagrid;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Pim\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;

/**
 * Flexible datagrid manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class FlexibleDatagridManager extends DatagridManager
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var AbstractAttribute[]
     */
    protected $attributes;

    /**
     * @var array
     */
    protected static $typeMatches = array(
        AbstractAttributeType::BACKEND_TYPE_DATE => array(
            'field'  => FieldDescriptionInterface::TYPE_DATE,
            'filter' => FilterInterface::TYPE_FLEXIBLE_DATE,
        ),
        AbstractAttributeType::BACKEND_TYPE_DATETIME => array(
            'field'  => FieldDescriptionInterface::TYPE_DATETIME,
            'filter' => FilterInterface::TYPE_FLEXIBLE_DATETIME,
        ),
        AbstractAttributeType::BACKEND_TYPE_DECIMAL => array(
            'field'  => FieldDescriptionInterface::TYPE_DECIMAL,
            'filter' => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        ),
        AbstractAttributeType::BACKEND_TYPE_BOOLEAN => array(
            'field'  => FieldDescriptionInterface::TYPE_BOOLEAN,
            'filter' => FilterInterface::TYPE_FLEXIBLE_BOOLEAN,
        ),
        AbstractAttributeType::BACKEND_TYPE_INTEGER => array(
            'field'  => FieldDescriptionInterface::TYPE_INTEGER,
            'filter' => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTION => array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_FLEXIBLE_OPTIONS,
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTIONS => array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_FLEXIBLE_OPTIONS,
        ),
        AbstractAttributeType::BACKEND_TYPE_TEXT => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_VARCHAR => array(
            'field' => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_PRICE => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_METRIC => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
    );

    /**
     * @param FlexibleManager $flexibleManager
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;

        $this->flexibleManager->setLocale($this->parameters->getLocale());
        $this->flexibleManager->setScope($this->parameters->getScope());
    }

    /**
     * Traverse all flexible attributes and add them as fields to collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     * @param array                      $options
     */
    protected function configureFlexibleFields(
        FieldDescriptionCollection $fieldsCollection,
        array $options = array()
    ) {
        foreach ($this->getFlexibleAttributes() as $attribute) {
            $attributeCode = $attribute->getCode();
            $fieldsCollection->add(
                $this->createFlexibleField(
                    $attribute,
                    isset($options[$attributeCode]) ? $options[$attributeCode] : array()
                )
            );
        }
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     * @param string                     $attributeCode
     * @param array                      $options
     */
    protected function configureFlexibleField(
        FieldDescriptionCollection $fieldsCollection,
        $attributeCode,
        array $options = array()
    ) {
        if ($this->hasFlexibleAttribute($attributeCode)) {
            $fieldsCollection->add(
                $this->createFlexibleField(
                    $this->getFlexibleAttribute($attributeCode),
                    $options
                )
            );
        }
    }

    /**
     * Create field by flexible attribute
     *
     * @param  AbstractAttribute         $attribute
     * @param  array                     $options
     * @return FieldDescriptionInterface
     */
    protected function createFlexibleField(AbstractAttribute $attribute, array $options = array())
    {
        $field = new FieldDescription();
        $field->setName($attribute->getCode());
        $field->setOptions($this->getFlexibleFieldOptions($attribute, $options));

        return $field;
    }

    /**
     * Get options for flexible field
     *
     * @param  AbstractAttribute $attribute
     * @param  array             $options
     * @return array
     */
    protected function getFlexibleFieldOptions(AbstractAttribute $attribute, array $options = array())
    {
        $defaultOptions = array(
            'label'         => ucfirst($attribute->getLabel()),
            'field_name'    => $attribute->getCode(),
            'required'      => false,
            'sortable'      => true,
            'filterable'    => true,
            'flexible_name' => $this->flexibleManager->getFlexibleName()
        );

        $result = array_merge($defaultOptions, $options);

        $backendType = $attribute->getBackendType();
        if (!isset($result['type'])) {
            $result['type'] = $this->convertFlexibleTypeToFieldType($backendType);
        }
        if (!isset($result['filter_type'])) {
            $result['filter_type'] = $this->convertFlexibleTypeToFilterType($backendType);
        }
        if (!isset($result['multiple']) && $result['type'] == FieldDescriptionInterface::TYPE_OPTIONS) {
            $result['multiple'] = true;
        }

        return $result;
    }

    /**
     * @return AbstractAttribute[]
     */
    protected function getFlexibleAttributes()
    {
        if (null === $this->attributes) {
            /** @var $attributeRepository \Doctrine\Common\Persistence\ObjectRepository */
            $attributeRepository = $this->flexibleManager->getAttributeRepository();
            $attributes = $attributeRepository->findBy(
                array('entityType' => $this->flexibleManager->getFlexibleName())
            );
            $this->attributes = array();
            /** @var $attribute AbstractAttribute */
            foreach ($attributes as $attribute) {
                $this->attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $this->attributes;
    }

    /**
     * @param  string            $code
     * @return AbstractAttribute
     * @throws \LogicException
     */
    protected function getFlexibleAttribute($code)
    {
        $attributes = $this->getFlexibleAttributes();
        if (!isset($attributes[$code])) {
            throw new \LogicException('There is no attribute with code "' . $code . '".');
        }

        return $attributes[$code];
    }

    /**
     * @param  string  $code
     * @return boolean
     */
    protected function hasFlexibleAttribute($code)
    {
        $attributes = $this->getFlexibleAttributes();

        return isset($attributes[$code]);
    }

    /**
     * @param $flexibleFieldType
     * @return string
     * @throws \LogicException
     */
    protected function convertFlexibleTypeToFieldType($flexibleFieldType)
    {
        if (!isset(static::$typeMatches[$flexibleFieldType]['field'])) {
            throw new \LogicException('Unknown flexible backend field type.');
        }

        return static::$typeMatches[$flexibleFieldType]['field'];
    }

    /**
     * @param $flexibleFieldType
     * @return string
     * @throws \LogicException
     */
    protected function convertFlexibleTypeToFilterType($flexibleFieldType)
    {
        if (!isset(static::$typeMatches[$flexibleFieldType]['filter'])) {
            throw new \LogicException('Unknown flexible backend filter type.');
        }

        return static::$typeMatches[$flexibleFieldType]['filter'];
    }
}
