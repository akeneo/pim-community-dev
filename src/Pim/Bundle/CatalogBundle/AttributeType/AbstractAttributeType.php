<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Abstract attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeType implements AttributeTypeInterface
{
    /**
     * Field backend type, "text" by default, the doctrine mapping field, getter / setter to use for binding
     *
     * @var string
     */
    protected $backendType = AttributeTypes::BACKEND_TYPE_TEXT;

    /**
     * Form type alias, "text" by default
     *
     * @var string
     */
    protected $formType = 'text';

    /**
     * Constructor
     *
     * @param string $backendType the backend type
     * @param string $formType    the form type
     */
    public function __construct($backendType, $formType)
    {
        $this->backendType = $backendType;
        $this->formType = $formType;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique()
    {
        return false;
    }

    /**
     * Get form type (alias)
     *
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * {@inheritdoc}
     */
    public function buildAttributeFormTypes(FormFactoryInterface $factory, AttributeInterface $attribute)
    {
        $properties = $this->defineCustomAttributeProperties($attribute);

        $types = [];

        foreach ($properties as $property) {
            $fieldType = 'text';
            if (isset($property['fieldType'])) {
                $fieldType = $property['fieldType'];
            }
            $data = null;
            if (isset($property['data'])) {
                $data = $property['data'];
            }
            $options = [];
            if (isset($property['options'])) {
                $options = $property['options'];
            }
            if (!isset($options['required'])) {
                $options['required'] = false;
            }
            $options['auto_initialize'] = false;

            $types[] = $factory->createNamed($property['name'], $fieldType, $data, $options);

            // Initialize the desired key in the properties array with an empty value
            // if it hasn't been set before to be able to dynamically display the field in the form
            if (!property_exists($attribute, $property['name']) &&
                !array_key_exists($property['name'], $attribute->getProperties())) {
                $attribute->setProperty($property['name'], null);
            }
        }

        return $types;
    }

    /**
     * Define custom properties used in attribute form
     *
     * Each property must be an array with a 'name' key that matches the name of the property
     * Optional 'fieldType', 'data' and 'options' keys can be provided for field customization
     *
     * @param AttributeInterface $attribute Attribute entity
     *
     * @return array:array:multitype $properties an array of custom properties
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return [
            'localizable' => [
                'name'      => 'localizable',
                'fieldType' => 'switch',
                'options'   => [
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            'availableLocales' => [
                'name'      => 'availableLocales',
                'fieldType' => 'pim_enrich_available_locales'
            ],
            'scopable' => [
                'name'      => 'scopable',
                'fieldType' => 'switch',
                'options'   => [
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            'unique' => [
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => [
                    'disabled'  => true,
                    'read_only' => true
                ]
            ]
        ];
    }
}
