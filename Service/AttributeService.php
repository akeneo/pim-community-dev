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
            array('name' => 'scopable', 'fieldType' => 'choice',
                'options' => array('choices' => array('Global', 'Channel'))),
            array('name' => 'translatable', 'fieldType' => 'checkbox'),
            array('name' => 'unique', 'fieldType' => 'checkbox')
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
            $field = $property;
            $field['data'] = null;
            $field['options']['disabled'] = $disabled;
            $fields[] = $field;
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
                $method = 'get' . ucfirst($property) . 'params';
                if (method_exists($this, $method)) {
                    $fields[] = $this->$method($attribute);
                }
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
     * Return activated properties for the attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return array Activated properties
     */
    public function getActivatedProperties($attribute)
    {
        $type = $attribute->getAttributeType();
        if (!$type) {
            return array();
        }
        $type = explode('\\', $attribute->getAttributeType());
        $type = substr(end($type), 0, -4);

        return array_key_exists($type, $this->config) ? $this->config[$type]['properties'] : array();
    }

    /**
     * Return base properties that apply to all attributes
     *
     * @return array Base properties
     */
    public function getBaseProperties()
    {
        return array(
            'code' => 'text',
            'attributeType' => 'text',
            'required' => 'boolean',
            'unique' => 'boolean',
            'searchable' => 'boolean',
            'translatable' => 'boolean',
            'scopable' => 'boolean',
            'name' => 'text',
            'description' => 'text',
            'variant' => 'integer',
            'smart' => 'boolean',
            'useableAsGridColumn' => 'boolean',
            'useableAsGridFilter' => 'boolean'
        );
    }

    /**
     * Return basic field parameters
     *
     * @param property $property
     *
     * @return array $params
     */
    private function getBaseFieldParams($property)
    {
        return array(
            'name' => $property,
            'fieldType' => 'text',
            'data' => null,
            'options' => array(
                'required' => false,
                'label' => $property
            )
        );
    }

    /**
     * Return form field parameters for defaultValue property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getDefaultValueParams($attribute)
    {
        $attTypeClass = $attribute->getAttributeType();
        $attType = new $attTypeClass();
        $fieldType = $attType->getFormType();

        if ($fieldType === 'entity') {
            $fieldType = 'text';
        } elseif ($attTypeClass == AbstractAttributeType::TYPE_BOOLEAN_CLASS) {
            $fieldType = 'checkbox';
        }

        $params = array('fieldType' => $fieldType);

        if ($attTypeClass == AbstractAttributeType::TYPE_DATE_CLASS) {
            $params['fieldType'] = $attribute->getDateType() ? $attribute->getDateType() : 'datetime';
            $params['options']['widget']  = 'single_text';
            if ($params['fieldType'] == 'date') {
                $params['options']['attr']  = array('data-format' => 'dd/MM/yyyy');
            } elseif ($params['fieldType'] == 'time') {
                $params['options']['attr']  = array('data-format' => 'hh:mm');
            } else {
                $params['options']['attr']  = array('data-format' => 'dd/MM/yyyy hh:mm');
            }
        }

        return array_merge($this->getBaseFieldParams('defaultValue'), $params);
    }

    /**
     * Return form field parameters for dateType property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getDateTypeParams($attribute)
    {
        $params = array(
            'fieldType' => 'choice',
            'options' => array(
                'required' => true,
                'choices' => array('date' => 'Date', 'time' => 'Time', 'datetime' => 'Datetime')
            )
        );

        return array_merge($this->getBaseFieldParams('dateType'), $params);
    }

    /**
     * Return form field parameters for dateMin property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getDateMinParams($attribute)
    {
        $fieldType = $attribute->getDateType() ? $attribute->getDateType() : 'datetime';

        if ($fieldType === 'date') {
            $attr  = array('data-format' => 'dd/MM/yyyy');
        } elseif ($fieldType === 'time') {
            $attr  = array('data-format' => 'hh:mm');
        } else {
            $attr  = array('data-format' => 'dd/MM/yyyy hh:mm');
        }

        $params = array(
            'fieldType' => $fieldType,
            'options' => array(
                'widget' => 'single_text',
                'attr' => $attr
            )
        );

        return array_merge($this->getBaseFieldParams('dateMin'), $params);
    }

    /**
     * Return form field parameters for dateMax property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getDateMaxParams($attribute)
    {
        $fieldType = $attribute->getDateType() ? $attribute->getDateType() : 'datetime';

        if ($fieldType === 'date') {
            $attr  = array('data-format' => 'dd/MM/yyyy');
        } elseif ($fieldType === 'time') {
            $attr  = array('data-format' => 'hh:mm');
        } else {
            $attr  = array('data-format' => 'dd/MM/yyyy hh:mm');
        }

        $params = array(
            'fieldType' => $fieldType,
            'options' => array(
                'widget' => 'single_text',
                'attr' => $attr
            )
        );

        return array_merge($this->getBaseFieldParams('dateMax'), $params);
    }

    /**
     * Return form field parameters for negativeAllowed property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getNegativeAllowedParams($attribute)
    {
        $params = array('fieldType' => 'checkbox');

        return array_merge($this->getBaseFieldParams('negativeAllowed'), $params);
    }

    /**
     * Return form field parameters for decimalsAllowed property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getDecimalsAllowedParams($attribute)
    {
        $params = array('fieldType' => 'checkbox');

        return array_merge($this->getBaseFieldParams('decimalsAllowed'), $params);
    }

    /**
     * Return form field parameters for numberMin property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getNumberMinParams($attribute)
    {
        $params = array('fieldType' => $attribute->getDecimalsAllowed() ? 'number' : 'integer');

        return array_merge($this->getBaseFieldParams('numberMin'), $params);
    }

    /**
     * Return form field parameters for numberMax property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getNumberMaxParams($attribute)
    {
        $params = array('fieldType' => $attribute->getDecimalsAllowed() ? 'number' : 'integer');

        return array_merge($this->getBaseFieldParams('numberMax'), $params);
    }

    /**
     * Return form field parameters for valueCreationAllowed property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getValueCreationAllowedParams($attribute)
    {
        $params = array('fieldType' => 'checkbox');

        return array_merge($this->getBaseFieldParams('valueCreationAllowed'), $params);
    }

    /**
     * Return form field parameters for maxCharacters property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getMaxCharactersParams($attribute)
    {
        $params = array('fieldType' => 'integer');

        return array_merge($this->getBaseFieldParams('maxCharacters'), $params);
    }

    /**
     * Return form field parameters for wysiwygEnabled property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getWysiwygEnabledParams($attribute)
    {
        $params = array('fieldType' => 'checkbox');

        return array_merge($this->getBaseFieldParams('wysiwygEnabled'), $params);
    }

    /**
     * Return form field parameters for metricType property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getMetricTypeParams($attribute)
    {
        return $this->getBaseFieldParams('metricType');
    }

    /**
     * Return form field parameters for defaultMetricUnit property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getDefaultMetricUnitParams($attribute)
    {
        return $this->getBaseFieldParams('defaultMetricUnit');
    }

    /**
     * Return form field parameters for allowedFileSources property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getAllowedFileSourcesParams($attribute)
    {
        $params = array(
            'fieldType' => 'choice',
            'options' => array(
                'required' => true,
                'choices' => array('upload' => 'Upload', 'external' => 'External')
            )
        );

        return array_merge($this->getBaseFieldParams('allowedFileSources'), $params);
    }

    /**
     * Return form field parameters for maxFileSize property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getMaxFileSizeParams($attribute)
    {
        $params = array('fieldType' => 'integer');

        return array_merge($this->getBaseFieldParams('maxFileSize'), $params);
    }

    /**
     * Return form field parameters for allowedFileExtensions property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getAllowedFileExtensionsParams($attribute)
    {
        $params = array(
            'data' => implode(',', $attribute->getAllowedFileExtensions()),
            'options' => array(
                'by_reference' => false,
                'attr' => array('class' => 'multiselect')
            )
        );

        return array_merge($this->getBaseFieldParams('allowedFileExtensions'), $params);
    }

    /**
     * Return form field parameters for validationRule property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getValidationRuleParams($attribute)
    {
        $params = array(
            'fieldType' => 'choice',
            'options' => array(
                'choices' => array(
                    null => 'None',
                    'email' => 'E-mail',
                    'url' => 'URL',
                    'regexp' => 'Regular expression'
                )
            )
        );

        return array_merge($this->getBaseFieldParams('validationRule'), $params);
    }

    /**
     * Return form field parameters for validationRegexp property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getValidationRegexpParams($attribute)
    {
        return $this->getBaseFieldParams('validationRegexp');
    }

    /**
     * Return form field parameters for unique property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getUniqueParams($attribute)
    {
        $params = array(
            'fieldType' => 'checkbox',
            'options' => array('disabled' => true)
        );

        return array_merge($this->getBaseFieldParams('unique'), $params);
    }

    /**
     * Return form field parameters for searchable property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getSearchableParams($attribute)
    {
        $params = array('fieldType' => 'checkbox');

        return array_merge($this->getBaseFieldParams('searchable'), $params);
    }
}
