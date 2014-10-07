<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
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
     * Available backend storage, the product doctrine mapped field
     *
     * @staticvar string
     */
    const BACKEND_STORAGE_ATTRIBUTE_VALUE = 'values';

    /**
     * Available backend types, the doctrine mapped field in value class
     *
     * @staticvar string
     */
    const BACKEND_TYPE_DATE       = 'date';
    const BACKEND_TYPE_DATETIME   = 'datetime';
    const BACKEND_TYPE_DECIMAL    = 'decimal';
    const BACKEND_TYPE_BOOLEAN    = 'boolean';
    const BACKEND_TYPE_INTEGER    = 'integer';
    const BACKEND_TYPE_OPTIONS    = 'options';
    const BACKEND_TYPE_OPTION     = 'option';
    const BACKEND_TYPE_TEXT       = 'text';
    const BACKEND_TYPE_VARCHAR    = 'varchar';
    const BACKEND_TYPE_MEDIA      = 'media';
    const BACKEND_TYPE_METRIC     = 'metric';
    const BACKEND_TYPE_PRICE      = 'prices';
    const BACKEND_TYPE_COLLECTION = 'collections';
    const BACKEND_TYPE_ENTITY     = 'entity';

    /**
     * Field backend type, "varchar" by default, the doctrine mapping field, getter / setter to use for binding
     *
     * @var string
     */
    protected $backendType = self::BACKEND_TYPE_VARCHAR;

    /**
     * Form type alias, "text" by default
     *
     * @var string
     */
    protected $formType = 'text';

    /**
     * Constructor
     *
     * @param string                     $backendType       the backend type
     * @param string                     $formType          the form type
     * @param ConstraintGuesserInterface $constraintGuesser the form type
     */
    public function __construct($backendType, $formType, ConstraintGuesserInterface $constraintGuesser)
    {
        $this->backendType       = $backendType;
        $this->formType          = $formType;
        $this->constraintGuesser = $constraintGuesser;
    }

    /**
     * Get backend type
     *
     * @return string
     */
    public function getBackendType()
    {
        return $this->backendType;
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

        $types = array();

        foreach ($properties as $property) {
            $fieldType = 'text';
            if (isset($property['fieldType'])) {
                $fieldType = $property['fieldType'];
            }
            $data = null;
            if (isset($property['data'])) {
                $data = $property['data'];
            }
            $options = array();
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
     * Get the value form type name to use to ensure binding
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function prepareValueFormName(ProductValueInterface $value)
    {
        return $value->getAttribute()->getBackendType();
    }

    /**
     * Get value form type alias to use to render value
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function prepareValueFormAlias(ProductValueInterface $value)
    {
        return $this->getFormType();
    }

    /**
     * Get value form type options to configure the form
     *
     * @param ProductValueInterface $value
     *
     * @return array
     */
    public function prepareValueFormOptions(ProductValueInterface $value)
    {
        return array(
            'label'           => $value->getAttribute()->getLabel(),
            'required'        => $value->getAttribute()->isRequired(),
            'auto_initialize' => false,
            'label_attr'      => ['truncate' => true]
        );
    }

    /**
     * Guess the constraints to apply on the form
     *
     * @param ProductValueInterface $value
     *
     * @return array
     */
    public function prepareValueFormConstraints(ProductValueInterface $value)
    {
        if ($this->constraintGuesser->supportAttribute($attribute = $value->getAttribute())) {
            return array(
                'constraints' => $this->constraintGuesser->guessConstraints($attribute),
            );
        }

        return array();
    }

    /**
     * Get value form type data
     *
     * @param ProductValueInterface $value
     *
     * @return mixed
     */
    public function prepareValueFormData(ProductValueInterface $value)
    {
        return is_null($value->getData()) ? $value->getAttribute()->getDefaultValue() : $value->getData();
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
                'fieldType' => 'pim_enrich_scopable',
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
