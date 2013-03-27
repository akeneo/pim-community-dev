<?php
namespace Pim\Bundle\ProductBundle\Service;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\BooleanType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\NumberType;

/**
 * Attribute Service
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeService
{
    /**
     * @var multitype
     */
    protected $config;

    /**
     * Constructor
     *
     * @param array $config Configuration parameters
     */
    public function __construct($config)
    {
        $this->config = $config['attributes_config'];
    }

    /**
     * Return an array of form field parameters for properties
     * that can't be changed once the attribute has been created
     *
     * @param ProductAttribute $attribute
     *
     * @return array $fields
     */
    public function getInitialFields($attribute = null)
    {
        $properties = array(
            array('name' => 'scopable', 'choices' => array('Global', 'Channel')),
            array('name' => 'translatable', 'choices' => array('No', 'Yes')),
            array('name' => 'unique', 'choices' => array('No', 'Yes'))
        );

        $disabled = false;
        if ($attribute !== null) {
            if ($attribute->getId()) {
                $disabled = true;
                if (!in_array('unique', $this->getActivatedProperties($attribute))) {
                    array_pop($properties);
                }
            }
        }

        $fields = array();
        foreach ($properties as $property) {
            $fields[] = array(
                'name' => $property['name'],
                'fieldType' => 'choice',
                'data' => null,
                'options' => array(
                    'choices' => $property['choices'],
                    'disabled' => $disabled
                )
            );
        }

        return $fields;
    }

    /**
     * Return an array of form field parameters for all custom properties
     *
     * @param ProductAttribute $attribute
     *
     * @return array|null $params
     */
    public function getCustomFields($attribute)
    {
        $properties = $this->getActivatedProperties($attribute);
        $fields = array();

        foreach ($properties as $property) {
            if ($property != 'unique') {
                $fields[] = $this->getFieldParams($attribute, $property);
            }
        }

        return $fields;
    }

    /**
     * Return an array of available attribute types
     *
     * @return array $types
     */
    public function getAttributeTypes()
    {
        $availableTypes = array(
            new BooleanType(),
            new DateType(),
            new FileType(),
            new ImageType(),
            new MetricType(),
            new MoneyType(),
            new OptionMultiSelectType(),
            new OptionSimpleSelectType(),
            new NumberType(),
            new TextAreaType(),
            new TextType(),
        );
        $types = array();
        foreach ($availableTypes as $type) {
            $name = explode('\\', get_class($type));
            $name = substr(end($name), 0, -4);
            if (array_key_exists($name, $this->config)) {
                $name = $this->config[$name]['name'];
            } else {
                $name = $type->getName();
            }
            $types[get_class($type)] = $name;
        }
        asort($types);

        return $types;
    }

    /**
     * Return form field parameters for a single property
     *
     * @param ProductAttribute $attribute
     * @param string           $property
     *
     * @return array $params
     */
    public function getFieldParams($attribute, $property)
    {
        $params = array('name' => $property, 'data' => null, 'options' => array('required' => false, 'label' => $property));
        switch ($property) {
            case 'defaultValue':
                $attTypeClass = $attribute->getAttributeType();
                $attType = new $attTypeClass();
                $fieldType = $attType->getFormType();

                if ($fieldType === 'entity') {
                    $fieldType = 'text';
                } elseif ($attTypeClass == AbstractAttributeType::TYPE_BOOLEAN_CLASS) {
                    $fieldType = 'choice';
                    $params['options']['choices'] = array(
                        0 => 'No',
                        1 => 'Yes'
                    );
                }
                $params['fieldType'] = $fieldType;
                break;
            case 'dateType':
                $params['fieldType']           = 'choice';
                $params['options']['choices']  = array('date' => 'Date', 'time' => 'Time', 'datetime' => 'Datetime');
                $params['options']['required'] = true;
                break;
            case 'dateMin':
                $params['fieldType'] = $attribute->getDateType() ? $attribute->getDateType() : 'datetime';
                break;
            case 'dateMax':
                $params['fieldType'] = $attribute->getDateType() ? $attribute->getDateType() : 'datetime';
                break;
            case 'negativeAllowed':
                $params['fieldType'] = 'choice';
                $params['options']['required'] = true;
                $params['options']['choices'] = array('No', 'Yes');
                break;
            case 'decimalPlaces':
                $params['fieldType']           = 'choice';
                $params['options']['required'] = true;
                $params['options']['choices']  = array(0, 1, 2, 3, 4);
                break;
            case 'numberMin':
                if ($attribute->getDecimalPlaces()) {
                    $params['fieldType']            = 'number';
                    $params['options']['precision'] = $attribute->getDecimalPlaces();
                } else {
                    $params['fieldType']            = 'integer';
                }
                break;
            case 'numberMax':
                if ($attribute->getDecimalPlaces()) {
                    $params['fieldType']            = 'number';
                    $params['options']['precision'] = $attribute->getDecimalPlaces();
                } else {
                    $params['fieldType']            = 'integer';
                }
                break;
            case 'valueCreationAllowed':
                $params['fieldType']           = 'choice';
                $params['options']['required'] = true;
                $params['options']['choices']  = array('No', 'Yes');
                break;
            case 'maxCharacters':
                $params['fieldType'] = 'integer';
                break;
            case 'wysiwygEnabled':
                $params['fieldType']          = 'choice';
                $params['options']['required'] = true;
                $params['options']['choices'] = array('No', 'Yes');
                break;
            case 'metricType':
                $params['fieldType'] = 'text';
                break;
            case 'defaultMetricUnit':
                $params['fieldType'] = 'text';
                break;
            case 'allowedFileSources':
                $params['fieldType']           = 'choice';
                $params['options']['required'] = true;
                $params['options']['choices']  = array('upload' => 'Upload', 'external' => 'External');
                break;
            case 'maxFileSize':
                $params['fieldType'] = 'integer';
                break;
            case 'allowedFileExtensions':
                $params['fieldType'] = 'text';
                $params['options']['by_reference'] = false;
                $params['data'] = implode(',', $attribute->getAllowedFileExtensions());
                break;
            case 'validationRule':
                $params['fieldType'] = 'choice';
                $params['options']['choices'] = array(null => 'None', 'email' => 'E-mail', 'url' => 'URL', 'regexp' => 'Regular expression');
                break;
            case 'validationRegexp':
                $params['fieldType'] = 'text';
                break;
            case 'unique':
                $params['fieldType'] = 'checkbox';
                $params['options']['disabled'] = true;
                $params['options']['choices']  = array('No', 'Yes');
                break;
            case 'searchable':
                $params['fieldType'] = 'checkbox';
                break;
            default:
                return null;
        }

        return $params;
    }

    /**
     * Return activated properties for the attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return array Activated properties
     */
    public function getActivatedProperties($attribute)
    {
        $type = $attribute->getAttributeType();
        $type = explode('\\', $attribute->getAttributeType());
        $type = substr(end($type), 0, -4);

        return array_key_exists($type, $this->config) ? $this->config[$type]['properties'] : array();
    }
}
