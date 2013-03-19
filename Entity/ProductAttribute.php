<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeExtended;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Pim\Bundle\ConfigBundle\Entity\Language;
use Pim\Bundle\ConfigBundle\Entity\Currency;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Custom properties for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product_attribute")
 * @ORM\Entity
 */
class ProductAttribute extends AbstractEntityAttributeExtended
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\OneToOne(
     *     targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute", cascade={"persist", "merge", "remove"}
     * )
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var string $variant
     *
     * @ORM\Column(name="variant", type="string", length=255)
     */
    protected $variant;

    /**
     * @var boolean $smart
     *
     * @ORM\Column(name="is_smart", type="boolean")
     */
    protected $smart;

    /**
     * @var AttributeGroup
     *
     * @ORM\ManyToOne(targetEntity="AttributeGroup", inversedBy="attributes")
     */
    protected $group;

    /**
     * @var boolean $useableAsGridColumn
     *
     * @ORM\Column(name="useable_as_grid_column", type="boolean", options={"default" = false})
     */
    protected $useableAsGridColumn;

    /**
     * @var boolean $useableAsGridFilter
     *
     * @ORM\Column(name="useable_as_grid_filter", type="boolean", options={"default" = false})
     */
    protected $useableAsGridFilter;

    /**
     * @var $availableLanguages ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ConfigBundle\Entity\Language")
     * @ORM\JoinTable(name="product_attribute_language")
     */
    protected $availableLanguages;

    /**
     * @var integer $maxCharacters
     *
     * @ORM\Column(name="max_characters", type="smallint", nullable=true)
     */
    protected $maxCharacters;

    /**
     * @var string $validationRule
     *
     * @ORM\Column(name="validation_rule", type="string", length=10, nullable=true)
     */
    protected $validationRule;

    /**
     * @var string $validationRegexp
     *
     * @ORM\Column(name="validation_regexp", type="string", length=255, nullable=true)
     */
    protected $validationRegexp;

    /**
     * @var boolean $wysiwygEnabled
     *
     * @ORM\Column(name="wysiwyg_enabled", type="boolean", nullable=true)
     */
    protected $wysiwygEnabled;

    /**
     * @var decimal $numberMin
     *
     * @ORM\Column(name="number_min", type="decimal", precision=14, scale=4, nullable=true)
     */
    protected $numberMin;

    /**
     * @var decimal $numberMax
     *
     * @ORM\Column(name="number_max", type="decimal", precision=14, scale=4, nullable=true)
     */
    protected $numberMax;

    /**
     * @var integer $decimalPlaces
     *
     * @ORM\Column(name="decimal_places", type="smallint", nullable=true)
     */
    protected $decimalPlaces;

    /**
     * @var boolean $negativeAllowed
     *
     * @ORM\Column(name="negative_allowed", type="boolean", nullable=true)
     */
    protected $negativeAllowed;

    /**
     * @var boolean $valueCreationAllowed
     *
     * @ORM\Column(name="value_creation_allowed", type="boolean", nullable=true)
     */
    protected $valueCreationAllowed;

    /**
     * @var string $dateType
     *
     * @ORM\Column(name="date_type", type="string", length=20, nullable=true)
     */
    protected $dateType;

    /**
     * @var decimal $dateMin
     *
     * @ORM\Column(name="date_min", type="datetime", nullable=true)
     */
    protected $dateMin;

    /**
     * @var decimal $dateMax
     *
     * @ORM\Column(name="date_max", type="datetime", nullable=true)
     */
    protected $dateMax;

    /**
     * @var Currency $defaultCurrency
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ConfigBundle\Entity\Currency")
     * @ORM\JoinColumn(name="default_currency_id", nullable=true, referencedColumnName="id")
     */
    protected $defaultCurrency;

    /**
     * @var string $metricType
     *
     * @ORM\Column(name="metric_type", type="string", length=20, nullable=true)
     */
    protected $metricType;

    /**
     * @var string $defaultMetricUnit
     *
     * @ORM\Column(name="default_metric_unit", type="string", length=10, nullable=true)
     */
    protected $defaultMetricUnit;

    /**
     * @var string $allowedFileSources
     *
     * @ORM\Column(name="allowed_file_sources", type="string", length=255, nullable=true)
     */
    protected $allowedFileSources;

    /**
     * @var integer $maxFileSize
     *
     * @ORM\Column(name="max_file_size", type="integer", nullable=true)
     */
    protected $maxFileSize;

    /**
     * @var array $allowedFileExtensions
     *
     * @ORM\Column(name="allowed_file_extensions", type="string", length=255, nullable=true)
     */
    protected $allowedFileExtensions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->description         = '';
        $this->smart               = false;
        $this->variant             = false;
        $this->useableAsGridColumn = false;
        $this->useableAsGridFilter = false;
        $this->availableLanguages  = new ArrayCollection();
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Return activated properties for the attribute
     *
     * @return array Activated properties
     */
    public function getActivatedProperties()
    {
        switch ($this->getAttribute()->getAttributeType()) {
            case AbstractAttributeType::TYPE_DATE_CLASS:
                return array('defaultValue', 'dateType', 'dateMin', 'dateMax');
            case AbstractAttributeType::TYPE_INTEGER_CLASS:
                return array('defaultValue', 'numberMin', 'numberMax', 'negativeAllowed');
            case AbstractAttributeType::TYPE_MONEY_CLASS:
                return array('defaultValue', 'numberMin', 'numberMax', 'decimalPlaces',
                    'negativeAllowed', 'defaultCurrency');
            case AbstractAttributeType::TYPE_NUMBER_CLASS:
                return array('defaultValue', 'numberMin', 'numberMax', 'decimalPlaces', 'negativeAllowed');
            case AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS:
                return array('valueCreationAllowed');
            case AbstractAttributeType::TYPE_OPT_SINGLE_SELECT_CLASS:
                return array('defaultValue');
            case AbstractAttributeType::TYPE_TEXTAREA_CLASS:
                return array('defaultValue', 'maxCharacters', 'wysiwygEnabled');
            case AbstractAttributeType::TYPE_METRIC_CLASS:
                return array('defaultValue', 'numberMin', 'numberMax', 'decimalPlaces',
                    'negativeAllowed', 'metricType', 'defaultMetricUnit');
            case AbstractAttributeType::TYPE_FILE_CLASS:
                return array('allowedFileSources', 'maxFileSize', 'allowedFileExtensions');
            case AbstractAttributeType::TYPE_IMAGE_CLASS:
                return array('allowedFileSources', 'maxFileSize', 'allowedFileExtensions');
            case AbstractAttributeType::TYPE_TEXT_CLASS:
                return array('defaultValue', 'maxCharacters', 'validationRule', 'validationRegexp');
            case AbstractAttributeType::TYPE_BOOLEAN_CLASS:
                return array('defaultValue');
            default:
                return array();
        }
    }

    /**
     * Return form field parameters for the property
     *
     * @param string $property
     *
     * @return array|null $params
     */
    public function getFieldParams($property)
    {
        $params = array('data' => null, 'options' => array('required' => false, 'label' => $property));
        switch ($property) {
            case 'defaultValue':
                $attribute = $this->getAttribute();
                $attTypeClass = $attribute->getAttributeType();
                $attType = new $attTypeClass();
                $fieldType = $attType->getFormType();

                if ($fieldType === 'entity') {
                    $fieldType = 'choice';
                    $params['options']['choices'] = array();
                    foreach ($attribute->getOptions() as $option) {
                        if ($option->getDefaultValue()) {
                            $params['options']['choices'][$option->getDefaultValue()] = $option->getDefaultValue();
                        }
                    }
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
                $params['fieldType'] = $this->dateType ? $this->dateType : 'datetime';
                break;
            case 'dateMax':
                $params['fieldType'] = $this->dateType ? $this->dateType : 'datetime';
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
                if ($this->decimalPlaces) {
                    $params['fieldType']            = 'number';
                    $params['options']['precision'] = $this->decimalPlaces;
                } else {
                    $params['fieldType']            = 'integer';
                }
                break;
            case 'numberMax':
                if ($this->decimalPlaces) {
                    $params['fieldType']            = 'number';
                    $params['options']['precision'] = $this->decimalPlaces;
                } else {
                    $params['fieldType']            = 'integer';
                }
                break;
            case 'defaultCurrency':
                $params['fieldType']        = 'entity';
                $params['options']['class'] = 'Pim\Bundle\ConfigBundle\Entity\Currency';
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
                $params['options']['choices']  = array('all' => 'All',
                    'upload' => 'Upload', 'external' => 'External');
                break;
            case 'maxFileSize':
                $params['fieldType'] = 'integer';
                break;
            case 'allowedFileExtensions':
                $params['fieldType'] = 'text';
                $params['options']['by_reference'] = false;
                $params['data'] = $this->allowedFileExtensions;
                break;
            case 'validationRule':
                $params['fieldType'] = 'choice';
                $params['options']['choices'] = array(null => 'None', 'email' => 'E-mail', 'url' => 'URL', 'regexp' => 'Regular expression');
                break;
            case 'validationRegexp':
                $params['fieldType'] = 'text';
                break;
            default:
                return null;
        }

        return $params;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProductAttribute
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ProductAttribute
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get smart
     *
     * @return boolean $smart
     */
    public function getSmart()
    {
        return $this->smart;
    }

    /**
     * Set smart
     *
     * @param boolean $smart
     *
     * @return ProductAttribute
     */
    public function setSmart($smart)
    {
        $this->smart = $smart;

        return $this;
    }

    /**
     * Get variant
     *
     * @return string $variant
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * Set variant
     *
     * @param string $variant
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    public function setVariant($variant)
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Get group
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param AttributeGroup $group
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    public function setGroup(AttributeGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get useableAsGridColumn
     *
     * @return boolean $useableAsGridColumn
     */
    public function getUseableAsGridColumn()
    {
        return $this->useableAsGridColumn;
    }

    /**
     * Set useableAsGridColumn
     *
     * @param boolean $useableAsGridColumn
     *
     * @return ProductAttribute
     */
    public function setUseableAsGridColumn($useableAsGridColumn)
    {
        $this->useableAsGridColumn = $useableAsGridColumn;

        return $this;
    }

    /**
     * Get useableAsGridFilter
     *
     * @return boolean $useableAsGridFilter
     */
    public function getUseableAsGridFilter()
    {
        return $this->useableAsGridFilter;
    }

    /**
     * Set useableAsGridFilter
     *
     * @param boolean $useableAsGridFilter
     *
     * @return ProductAttribute
     */
    public function setUseableAsGridFilter($useableAsGridFilter)
    {
        $this->useableAsGridFilter = $useableAsGridFilter;

        return $this;
    }

    /**
     * Add available language
     *
     * @param Language $availableLanguage
     *
     * @return ProductAttribute
     */
    public function addAvailableLanguage(Language $availableLanguage)
    {
        $this->availableLanguages[] = $availableLanguage;

        return $this;
    }

    /**
     * Remove available language
     *
     * @param Language $availableLanguage
     *
     * @return ProductAttribute
     */
    public function removeAvailableLanguage(Language $availableLanguage)
    {
        $this->availableLanguages->removeElement($availableLanguage);

        return $this;
    }

    /**
     * Get available languages
     *
     * @return ArrayCollection|null
     */
    public function getAvailableLanguages()
    {
        return $this->availableLanguages->isEmpty() ? null : $this->availableLanguages;
    }

    /**
     * Get Max characters
     *
     * @return integer $maxCharacters
     */
    public function getMaxCharacters()
    {
        return $this->maxCharacters;
    }

    /**
     * Set Max characters
     *
     * @param integer $maxCharacters
     *
     * @return ProductAttribute
     */
    public function setMaxCharacters($maxCharacters)
    {
        $this->maxCharacters = $maxCharacters;

        return $this;
    }

    /**
     * Get Validation rule
     *
     * @return string $validationRule
     */
    public function getValidationRule()
    {
        return $this->validationRule;
    }

    /**
     * Set Validation rule
     *
     * @param string $validationRule
     *
     * @return ProductAttribute
     */
    public function setValidationRule($validationRule)
    {
        $this->validationRule = $validationRule;

        return $this;
    }

    /**
     * Get Validation regexp
     *
     * @return string $validationRegexp
     */
    public function getValidationRegexp()
    {
        return $this->validationRegexp;
    }

    /**
     * Set Validation regexp
     *
     * @param string $validationRegexp
     *
     * @return ProductAttribute
     */
    public function setValidationRegexp($validationRegexp)
    {
        $this->validationRegexp = $validationRegexp;

        return $this;
    }

    /**
     * Get wysiwygEnabled
     *
     * @return boolean $wysiwygEnabled
     */
    public function getWysiwygEnabled()
    {
        return $this->wysiwygEnabled;
    }

    /**
     * Set wysiwygEnabled
     *
     * @param boolean $wysiwygEnabled
     *
     * @return ProductAttribute
     */
    public function setWysiwygEnabled($wysiwygEnabled)
    {
        $this->wysiwygEnabled = $wysiwygEnabled;

        return $this;
    }

    /**
     * Get numberMin
     *
     * @return mixed $numberMin
     */
    public function getNumberMin()
    {
        return $this->numberMin;
    }

    /**
     * Set numberMin
     *
     * @param mixed $numberMin
     *
     * @return ProductAttribute
     */
    public function setNumberMin($numberMin)
    {
        $this->numberMin = $numberMin;

        return $this;
    }

    /**
     * Get numberMax
     *
     * @return mixed $numberMax
     */
    public function getNumberMax()
    {
        return $this->numberMax;
    }

    /**
     * Set numberMax
     *
     * @param mixed $numberMax
     *
     * @return ProductAttribute
     */
    public function setNumberMax($numberMax)
    {
        $this->numberMax = $numberMax;

        return $this;
    }

    /**
     * Get decimalPlaces
     *
     * @return integer $decimalPlaces
     */
    public function getDecimalPlaces()
    {
        return $this->decimalPlaces;
    }

    /**
     * Set decimalPlaces
     *
     * @param integer $decimalPlaces
     *
     * @return ProductAttribute
     */
    public function setDecimalPlaces($decimalPlaces)
    {
        $this->decimalPlaces = $decimalPlaces;

        return $this;
    }

    /**
     * Get negativeAllowed
     *
     * @return boolean $negativeAllowed
     */
    public function getNegativeAllowed()
    {
        return $this->negativeAllowed;
    }

    /**
     * Set negativeAllowed
     *
     * @param boolean $negativeAllowed
     *
     * @return ProductAttribute
     */
    public function setNegativeAllowed($negativeAllowed)
    {
        $this->negativeAllowed = $negativeAllowed;

        return $this;
    }

    /**
     * Get valueCreationAllowed
     *
     * @return boolean $valueCreationAllowed
     */
    public function getValueCreationAllowed()
    {
        return $this->valueCreationAllowed;
    }

    /**
     * Set valueCreationAllowed
     *
     * @param boolean $valueCreationAllowed
     *
     * @return ProductAttribute
     */
    public function setValueCreationAllowed($valueCreationAllowed)
    {
        $this->valueCreationAllowed = $valueCreationAllowed;

        return $this;
    }

    /**
     * Get dateType
     *
     * @return string $dateType
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * Set dateType
     *
     * @param string $dateType
     *
     * @return ProductAttribute
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;

        return $this;
    }

    /**
     * Get dateMin
     *
     * @return datetime $dateMin
     */
    public function getDateMin()
    {
        return $this->dateMin;
    }

    /**
     * Set dateMin
     *
     * @param datetime $dateMin
     *
     * @return ProductAttribute
     */
    public function setDateMin($dateMin)
    {
        $this->dateMin = $dateMin;

        return $this;
    }

    /**
     * Get dateMax
     *
     * @return datetime $dateMax
     */
    public function getDateMax()
    {
        return $this->dateMax;
    }

    /**
     * Set dateMax
     *
     * @param datetime $dateMax
     *
     * @return ProductAttribute
     */
    public function setDateMax($dateMax)
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    /**
     * Get defaultCurrency
     *
     * @return Currency $defaultCurrency
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }

    /**
     * Set defaultCurrency
     *
     * @param Currency $defaultCurrency
     *
     * @return ProductAttribute
     */
    public function setDefaultCurrency(Currency $defaultCurrency = null)
    {
        $this->defaultCurrency = $defaultCurrency;

        return $this;
    }

    /**
     * Get metricType
     *
     * @return string $metricType
     */
    public function getMetricType()
    {
        return $this->metricType;
    }

    /**
     * Set metricType
     *
     * @param string $metricType
     *
     * @return ProductAttribute
     */
    public function setMetricType($metricType)
    {
        $this->metricType = $metricType;

        return $this;
    }

    /**
     * Get defaultMetricUnit
     *
     * @return string $defaultMetricUnit
     */
    public function getDefaultMetricUnit()
    {
        return $this->defaultMetricUnit;
    }

    /**
     * Set defaultMetricUnit
     *
     * @param string $defaultMetricUnit
     *
     * @return ProductAttribute
     */
    public function setDefaultMetricUnit($defaultMetricUnit)
    {
        $this->defaultMetricUnit = $defaultMetricUnit;

        return $this;
    }

    /**
     * Get allowedFileSources
     *
     * @return array $allowedFileSources
     */
    public function getAllowedFileSources()
    {
        return $this->allowedFileSources;
    }

    /**
     * Set allowedFileSources
     *
     * @param array $allowedFileSources
     *
     * @return ProductAttribute
     */
    public function setAllowedFileSources($allowedFileSources)
    {
        $this->allowedFileSources = $allowedFileSources;

        return $this;
    }

    /**
     * Get maxFileSize
     *
     * @return integer $maxFileSize
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * Set maxFileSize
     *
     * @param integer $maxFileSize
     *
     * @return ProductAttribute
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Get allowedFileExtensions
     *
     * @return array $allowedFileExtensions
     */
    public function getAllowedFileExtensions()
    {
        return $this->allowedFileExtensions ? explode(',', $this->allowedFileExtensions) : array();
    }

    /**
     * Set allowedFileExtensions
     *
     * @param array $allowedFileExtensions
     *
     * @return ProductAttribute
     */
    public function setAllowedFileExtensions($allowedFileExtensions)
    {
        $this->allowedFileExtensions = is_array($allowedFileExtensions) ? implode(',', $allowedFileExtensions) : $allowedFileExtensions;

        return $this;
    }
}
