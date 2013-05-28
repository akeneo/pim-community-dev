<?php
namespace Pim\Bundle\ProductBundle\Service;

use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\AttributeOption;
use Pim\Bundle\ProductBundle\Entity\AttributeOptionValue;
use Pim\Bundle\ProductBundle\Form\Type\AttributeOptionType;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;

use Doctrine\ORM\EntityRepository;

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
     * @var AttributeTypeFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param array                $config        Configuration parameters
     * @param ProductManager       $manager       Product manager
     * @param LocaleManager        $localeManager Locale manager
     * @param AttributeTypeFactory $factory       Attribute type factory
     */
    public function __construct($config, ProductManager $manager, LocaleManager $localeManager, AttributeTypeFactory $factory)
    {
        $this->config = $config['attributes_config'];
        $this->manager = $manager;
        $this->localeManager = $localeManager;
        $this->factory = $factory;
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
        if ($data instanceof ProductAttribute) {
            return $data;
        }

        if (gettype($data) === 'array' && isset($data['attributeType'])) {
            return $this->manager->createAttribute($data['attributeType']);
        } elseif (gettype($data) === 'array' && isset($data['id'])) {
            return $this->manager->getAttributeRepository()->find($data['id']);
        } else {
            return null;
        }
    }

    /**
     * Prepare data for binding to the form
     *
     * @param array $data Form data
     *
     * @return array Prepared form data
     */
    public function prepareFormData($data)
    {
        $optionTypes = array(
            'pim_product_multiselect',
            'pim_product_simpleselect'
        );

        // If the attribute type can have options but no options have been created,
        // create an empty option to render the corresponding form fields
        if (in_array($data['attributeType'], $optionTypes) && !isset($data['options'])) {
            $option = array(
                'optionValues' => array()
            );

            foreach ($this->localeManager->getActiveLocales() as $locale) {
                $option['optionValues'][] = array(
                    'locale' => $locale->getCode()
                );
            }

            $data['options'] = array($option);
        }

        return $data;
    }

    /**
     * Return an array of form field parameters for attribute parameters
     *
     * @param ProductAttribute $attribute
     *
     * @return array $fields
     */
    public function getParameterFields($attribute = null)
    {
        $parameters = array('translatable', 'scopable', 'unique', 'availableLocales');
        $activatedParameters = $this->getActivatedParameters($attribute);
        $fields = array();

        foreach ($parameters as $parameter) {
            $method = 'get' . ucfirst($parameter) . 'params';
            if (method_exists($this, $method)) {
                $field = $this->$method($attribute);
                if (!in_array($parameter, $activatedParameters)) {
                    $field['options']['disabled'] = true;
                    $field['options']['read_only'] = true;
                }
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Return an array of form field parameters for attribute properties
     *
     * @param ProductAttribute $attribute
     *
     * @return array $fields
     */
    public function getPropertyFields($attribute = null)
    {
        $properties = $this->getActivatedProperties($attribute);
        $fields = array();

        foreach ($properties as $property) {
            $method = 'get' . ucfirst($property) . 'params';
            if (method_exists($this, $method)) {
                $fields[] = $this->$method($attribute);
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
        $types = $this->manager->getAttributeTypes();
        $choice = array();
        foreach ($types as $type) {
            $choice[$type]= $type;
        }
        asort($choice);

        return $choice;
    }

    /**
     * Return activated parameters for the attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return array Activated parameters
     */
    private function getActivatedParameters($attribute)
    {
        $type = $attribute->getAttributeType();
        if (!$type) {
            return array();
        }

        return array_key_exists($type, $this->config) ? $this->config[$type]['parameters'] : array();
    }

    /**
     * Return activated properties for the attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return array Activated properties
     */
    private function getActivatedProperties($attribute)
    {
        $type = $attribute->getAttributeType();
        if (!$type) {
            return array();
        }

        return array_key_exists($type, $this->config) ? $this->config[$type]['properties'] : array();
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
        $type = $attribute->getAttributeType();
        $fieldType = $this->factory->get($type)->getFormType();

        if ($type === 'oro_flexibleentity_metric') {
            $fieldType = 'text';
        } elseif ($type === 'pim_product_price_collection') {
            $fieldType = 'number';
        } elseif ($type === 'oro_flexibleentity_boolean') {
            $fieldType = 'checkbox';
        }

        $options = array();

        if ($fieldType === 'entity') {
            $options['empty_value']   = null;
            $options['class']         = 'PimProductBundle:AttributeOption';
            $options['query_builder'] = function (EntityRepository $er) use ($attribute) {
                return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
            };
            $options['expanded'] = false;
            $options['multiple'] = $type === 'pim_product_multiselect';
        }

        if ($type === 'oro_flexibleentity_date') {
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
     * Return form field parameters for metricFamily property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getMetricFamilyParams($attribute)
    {
        return $this->getFieldParams('metricFamily');
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

    /**
     * Return form field parameters for options property
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getOptionsParams($attribute)
    {
        $fieldType = 'collection';
        $options = array(
            'type'         => new AttributeOptionType(),
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false
        );

        return $this->getFieldParams('options', $fieldType, null, $options);
    }

    /**
     * Return form field parameters for translatable parameter
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getTranslatableParams($attribute)
    {
        return $this->getFieldParams('translatable', 'checkbox');
    }

    /**
     * Return form field parameters for scopable parameter
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getScopableParams($attribute)
    {
        $fieldType = 'choice';
        $options = array(
            'choices' => array('Global', 'Channel'),
            'disabled' => (bool) $attribute->getId(),
            'read_only' => (bool) $attribute->getId()
        );

        return $this->getFieldParams('scopable', $fieldType, null, $options);
    }

    /**
     * Return form field parameters for unique parameter
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getUniqueParams($attribute)
    {
        $fieldType = 'checkbox';
        $options = array(
            'disabled' => (bool) $attribute->getId(),
            'read_only' => (bool) $attribute->getId()
        );

        return $this->getFieldParams('unique', $fieldType, null, $options);
    }

    /**
     * Return form field parameters for available locales parameter
     *
     * @param ProductAttribute $attribute Product attribute
     *
     * @return array $params
     */
    private function getAvailableLocalesParams($attribute)
    {
        $fieldType = 'entity';
        $options = array(
            'required' => false,
            'multiple' => true,
            'class' => 'Pim\Bundle\ConfigBundle\Entity\Locale',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('l')->where('l.activated = 1')->orderBy('l.code');
            }
        );

        return $this->getFieldParams('availableLocales', $fieldType, null, $options);
    }
}
