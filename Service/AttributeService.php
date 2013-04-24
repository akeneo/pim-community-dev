<?php
namespace Pim\Bundle\ProductBundle\Service;

use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\AttributeOption;
use Pim\Bundle\ProductBundle\Entity\AttributeOptionValue;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

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
     * @var ProductManager
     */
    protected $manager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * Constructor
     *
     * @param array          $config        Configuration parameters
     * @param ProductManager $manager       Product manager
     * @param LocaleManager  $localeManager Locale manager
     */
    public function __construct($config, ProductManager $manager, LocaleManager $localeManager)
    {
        $this->config = $config['attributes_config'];
        $this->manager = $manager;
        $this->localeManager = $localeManager;
    }

    /**
     * Create a ProductAttribute object from data in the form
     *
     * @param array $data Form data
     *
     * @return ProductAttribute $attribute | null
     */
    public function createAttributeFromFormData($data)
    {
        if (gettype($data) === 'array') {
            $type = !empty($data['attributeType']) ? new $data['attributeType']() : null;
            $attribute = $this->manager->createAttribute($type);

            $baseProperties = $this->getBaseProperties();

            foreach ($data as $property => $value) {
                if (array_key_exists($property, $baseProperties) && $value !== '') {
                    $set = 'set' . ucfirst($property);
                    if (method_exists($attribute, $set)) {
                        if ($baseProperties[$property] === 'boolean') {
                            $value = (bool) $value;
                        } elseif ($baseProperties[$property] === 'integer') {
                            $value = (int) $value;
                        }
                        $attribute->$set($value);
                    }
                }
            }

            if ($attribute->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTION
                || $attribute->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTIONS) {

                $option = new AttributeOption();
                $option->setTranslatable(true);
                foreach ($this->localeManager->getActiveLocales() as $locale) {
                    $optionValue = new AttributeOptionValue();
                    $optionValue->setLocale($locale->getCode());

                    $option->addOptionValue($optionValue);
                }
                $attribute->addOption($option);
            }

            return $attribute;
        } elseif ($data instanceof ProductAttribute) {

            return $data;
        }

        return null;
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
     * Return field parameters based on provided data
     *
     * @param name      $name
     * @param fieldType $fieldType
     * @param data      $data
     * @param options   $options
     *
     * @return array $params
     */
    private function getFieldParams($name, $fieldType = 'text', $data = null, $options = array())
    {
        $baseOptions = array('required' => false, 'label' => $name);
        $options = array_merge($baseOptions, $options);

        return array(
            'name' => $name,
            'fieldType' => $fieldType,
            'data' => $data,
            'options' => $options
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

        if ($fieldType === 'entity' || $fieldType = 'oro_flexibleentity_metric') {
            $fieldType = 'text';
        } elseif ($attTypeClass == AbstractAttributeType::TYPE_BOOLEAN_CLASS) {
            $fieldType = 'checkbox';
        }

        $options = array();

        if ($attTypeClass == AbstractAttributeType::TYPE_DATE_CLASS) {
            $fieldType = $attribute->getDateType() ? $attribute->getDateType() : 'datetime';
            $options['widget'] = 'single_text';
            if ($fieldType == 'date') {
                $options['attr'] = array('data-format' => 'dd/MM/yyyy');
            } elseif ($fieldType == 'time') {
                $options['attr'] = array('data-format' => 'hh:mm');
            } else {
                $options['attr'] = array('data-format' => 'dd/MM/yyyy hh:mm');
            }
        }

        return $this->getFieldParams('defaultValue', $fieldType, null, $options);
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
        $fieldType = 'choice';
        $options = array(
            'required' => true,
            'choices' => array('date' => 'Date', 'time' => 'Time', 'datetime' => 'Datetime')
        );

        return $this->getFieldParams('dateType', $fieldType, null, $options);
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

        $options = array(
            'widget' => 'single_text'
        );

        if ($fieldType === 'date') {
            $options['attr']  = array('data-format' => 'dd/MM/yyyy');
        } elseif ($fieldType === 'time') {
            $options['attr']  = array('data-format' => 'hh:mm');
        } else {
            $options['attr']  = array('data-format' => 'dd/MM/yyyy hh:mm');
        }

        return $this->getFieldParams('dateMin', $fieldType, null, $options);
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

        $options = array(
            'widget' => 'single_text'
        );

        if ($fieldType === 'date') {
            $options['attr']  = array('data-format' => 'dd/MM/yyyy');
        } elseif ($fieldType === 'time') {
            $options['attr']  = array('data-format' => 'hh:mm');
        } else {
            $options['attr']  = array('data-format' => 'dd/MM/yyyy hh:mm');
        }

        return $this->getFieldParams('dateMax', $fieldType, null, $options);
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
        return $this->getFieldParams('negativeAllowed', 'checkbox');
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
        return $this->getFieldParams('decimalsAllowed', 'checkbox');
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
        $fieldType = $attribute->getDecimalsAllowed() ? 'number' : 'integer';

        return $this->getFieldParams('numberMin', $fieldType);
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
        $fieldType = $attribute->getDecimalsAllowed() ? 'number' : 'integer';

        return $this->getFieldParams('numberMax', $fieldType);
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
        return $this->getFieldParams('valueCreationAllowed', 'checkbox');
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
        return $this->getFieldParams('maxCharacters', 'integer');
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
        return $this->getFieldParams('wysiwygEnabled', 'checkbox');
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
        return $this->getFieldParams('metricType');
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
        return $this->getFieldParams('defaultMetricUnit');
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
        $fieldType = 'choice';
        $options = array(
            'required' => true,
            'choices' => array('upload' => 'Upload', 'external' => 'External')
        );

        return $this->getFieldParams('allowedFileSources', $fieldType, null, $options);
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
        return $this->getFieldParams('maxFileSize', 'integer');
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
        $fieldType = 'text';
        $data = implode(',', $attribute->getAllowedFileExtensions());
        $options = array(
            'by_reference' => false,
            'attr' => array('class' => 'multiselect')
        );

        return $this->getFieldParams('allowedFileExtensions', $fieldType, $data, $options);
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
        $fieldType = 'choice';
        $options = array(
            'choices' => array(
                null => 'None',
                'email' => 'E-mail',
                'url' => 'URL',
                'regexp' => 'Regular expression'
            )
        );

        return $this->getFieldParams('validationRule', $fieldType, null, $options);
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
        return $this->getFieldParams('validationRegexp');
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
        $fieldType = 'checkbox';
        $options = array('disabled' => true, 'read_only' => true);

        return $this->getFieldParams('unique', $fieldType, null, $options);
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
        return $this->getFieldParams('searchable', 'checkbox');
    }
}
