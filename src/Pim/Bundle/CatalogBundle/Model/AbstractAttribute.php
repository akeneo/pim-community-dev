<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;

/**
 * Abstract product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttribute implements TimestampableInterface, TranslatableInterface,
 GroupSequenceProviderInterface, ReferableInterface, VersionableInterface
{
    /**
     * Attribute id
     * @var integer $id
     */
    protected $id;

    /**
     * Attribute code
     * @var string $code
     */
    protected $code;

    /**
     * Attribute label
     * @var string $label
     */
    protected $label;

    /**
     * Entity type (FQCN)
     * @var string $entityType
     */
    protected $entityType;

    /**
     * Attribute type (service alias))
     * @var string $attributeType
     */
    protected $attributeType;

    /**
     * Kind of field to store values
     * @var string $backendType
     */
    protected $backendType;

    /**
     * @var datetime $created
     */
    protected $created;

    /**
     * @var datetime $created
     */
    protected $updated;

    /**
     * Is attribute is required
     * @var boolean $required
     */
    protected $required;

    /**
     * Is attribute value is required
     * @var boolean $unique
     */
    protected $unique;

    /**
     * Default attribute value
     * @var string $defaultValue
     */
    protected $defaultValue;

    /**
    * @var boolean $localizable
    */
    protected $localizable;

    /**
     * @var boolean $scopable
     */
    protected $scopable;

    /**
     * @var array $properties
     */
    protected $properties;

    /**
     * @var ArrayCollection $options
     */
    protected $options;

    /** @var integer $sortOrder */
    protected $sortOrder = 0;

    /** @var AttributeGroup $group */
    protected $group;

    /** @var boolean $useableAsGridColumn */
    protected $useableAsGridColumn;

    /** @var boolean $useableAsGridFilter */
    protected $useableAsGridFilter;

    /** @var ArrayCollection $availableLocales */
    protected $availableLocales;

    /** @var ArrayCollection $families */
    protected $families;

    /** @var integer $maxCharacters */
    protected $maxCharacters;

    /** @var string $validationRule */
    protected $validationRule;

    /** @var string $validationRegexp */
    protected $validationRegexp;

    /** @var boolean $wysiwygEnabled */
    protected $wysiwygEnabled;

    /** @var decimal $numberMin */
    protected $numberMin;

    /** @var decimal $numberMax */
    protected $numberMax;

    /** @var boolean $decimalsAllowed */
    protected $decimalsAllowed;

    /** @var boolean $negativeAllowed */
    protected $negativeAllowed;

    /** @var datetime $dateMin */
    protected $dateMin;

    /** @var datetime $dateMax */
    protected $dateMax;

    /** @var string $metricFamily */
    protected $metricFamily;

    /** @var string $defaultMetricUnit */
    protected $defaultMetricUnit;

    /**
     * @var decimal $maxFileSize
     * expressed in MB so decimal is needed for values < 1 MB
     */
    protected $maxFileSize;

    /** @var array $allowedExtensions */
    protected $allowedExtensions;

    /** @var integer $minimumInputLength */
    protected $minimumInputLength = 0;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale;

    /** @var ArrayCollection $translations */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options             = new ArrayCollection();
        $this->required            = false;
        $this->unique              = false;
        $this->defaultValue        = null;
        $this->localizable         = false;
        $this->scopable            = false;
        $this->useableAsGridColumn = false;
        $this->useableAsGridFilter = false;
        $this->availableLocales    = new ArrayCollection();
        $this->families            = new ArrayCollection();
        $this->translations        = new ArrayCollection();
        $this->validationRule      = null;
        $this->properties          = array();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractAttribute
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return AbstractAttribute
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set entity type
     *
     * @param string $entityType
     *
     * @return AbstractAttribute
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Get created datetime
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param datetime $created
     *
     * @return TimestampableInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param datetime $updated
     *
     * @return TimestampableInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Set backend type
     *
     * @param string $type
     *
     * @return AbstractAttribute
     */
    public function setBackendType($type)
    {
        $this->backendType = $type;

        return $this;
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
     * Get frontend type
     *
     * @return string
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * Set required
     *
     * @param boolean $required
     *
     * @return AbstractAttribute
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Is required
     *
     * @return boolean $required
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Set unique
     *
     * @param boolean $unique
     *
     * @return AbstractAttribute
     */
    public function setUnique($unique)
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * Is unique
     *
     * @return boolean $unique
     */
    public function isUnique()
    {
        return $this->unique;
    }

    /**
     * Set localizable
     *
     * @param boolean $localizable
     *
     * @return AbstractAttribute
     */
    public function setLocalizable($localizable)
    {
        $this->localizable = $localizable;

        return $this;
    }

    /**
     * Is localizable
     *
     * @return boolean $localizable
     */
    public function isLocalizable()
    {
        return $this->localizable;
    }

    /**
     * Set scopable
     *
     * @param boolean $scopable
     *
     * @return AbstractAttribute
     */
    public function setScopable($scopable)
    {
        $this->scopable = $scopable;

        return $this;
    }

    /**
     * Is scopable
     *
     * @return boolean $scopable
     */
    public function isScopable()
    {
        return $this->scopable;
    }

    /**
     * Add option
     *
     * @param AttributeOption $option
     *
     * @return AbstractAttribute
     */
    public function addOption(AttributeOption $option)
    {
        $this->options[] = $option;
        $option->setAttribute($this);

        return $this;
    }

    /**
     * Remove option
     *
     * @param AttributeOption $option
     *
     * @return AbstractAttribute
     */
    public function removeOption(AttributeOption $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get options
     *
     * @return \ArrayAccess
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set properties
     *
     * @param array $properties
     *
     * @return AbstractAttribute
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Get a property
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty($property)
    {
        return isset($this->properties[$property]) ? $this->properties[$property] : null;
    }

    /**
     * Set a property
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return AbstractAttribute
     */
    public function setProperty($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * Return the identifier-based validation group for validation of properties
     * @return string[]
     */
    public function getGroupSequence()
    {
        $groups = array('Default', $this->getAttributeType());

        if ($this->isUnique()) {
            $groups[] = 'unique';
        }
        if ($this->isScopable()) {
            $groups[] = 'scopable';
        }
        if ($this->isScopable()) {
            $groups[] = 'localizable';
        }
        if ($rule = $this->getValidationRule()) {
            $groups[] = $rule;
        }

        return $groups;
    }

    /**
     * Get default value
     *
     * @return mixed $defaultValue
     */
    public function getDefaultValue()
    {
        $default = $this->defaultValue;

        switch ($this->getBackendType()) {
            case 'option':
                $default = $this->getDefaultOptions()->first();
                if (false === $default) {
                    $default = null;
                }
                break;
            case 'options':
                $default = $this->getDefaultOptions();
                break;
            case 'date':
                if (null !== $this->defaultValue) {
                    $default = new \DateTime();
                    $default->setTimestamp((int) $this->defaultValue);
                }
                break;
            case 'boolean':
                if (null !== $this->defaultValue) {
                    $default = (bool) $this->defaultValue;
                }
                break;
        }

        return $default;
    }

    /**
     * Set default value
     *
     * @param mixed $defaultValue
     *
     * @return AbstractAttribute
     */
    public function setDefaultValue($defaultValue)
    {
        if (is_null($defaultValue)
            || ($defaultValue instanceof ArrayCollection && $defaultValue->isEmpty())) {
            $this->defaultValue = null;
        } else {
            switch ($this->getBackendType()) {
                case 'date':
                    $this->defaultValue = $defaultValue->format('U');
                    break;
                case 'boolean':
                    $this->defaultValue = (int) $defaultValue;
                    break;
                default:
                    $this->defaultValue = $defaultValue;
                    break;
            }
        }

        return $this;
    }

    /**
     * Get default AttributeOptions
     *
     * @return ArrayCollection
     */
    public function getDefaultOptions()
    {
        return $this->options->filter(
            function ($option) {
                return $option->isDefault();
            }
        );
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * Get group
     *
     * @return AttributeGroup
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
     * @return AbstractAttribute
     */
    public function setGroup(AttributeGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Predicate for useableAsGridColumn property
     *
     * @return boolean $useableAsGridColumn
     */
    public function isUseableAsGridColumn()
    {
        return $this->useableAsGridColumn;
    }

    /**
     * Set useableAsGridColumn
     *
     * @param boolean $useableAsGridColumn
     *
     * @return AbstractAttribute
     */
    public function setUseableAsGridColumn($useableAsGridColumn)
    {
        $this->useableAsGridColumn = $useableAsGridColumn;

        return $this;
    }

    /**
     * Predicate for useableAsGridFilter property
     *
     * @return boolean $useableAsGridFilter
     */
    public function isUseableAsGridFilter()
    {
        return $this->useableAsGridFilter;
    }

    /**
     * Set useableAsGridFilter
     *
     * @param boolean $useableAsGridFilter
     *
     * @return AbstractAttribute
     */
    public function setUseableAsGridFilter($useableAsGridFilter)
    {
        $this->useableAsGridFilter = $useableAsGridFilter;

        return $this;
    }

    /**
     * Add available locale
     *
     * @param Locale $availableLocale
     *
     * @return AbstractAttribute
     */
    public function addAvailableLocale(Locale $availableLocale)
    {
        $this->availableLocales[] = $availableLocale;

        return $this;
    }

    /**
     * Remove available locale
     *
     * @param Locale $availableLocale
     *
     * @return AbstractAttribute
     */
    public function removeAvailableLocale(Locale $availableLocale)
    {
        $this->availableLocales->removeElement($availableLocale);

        return $this;
    }

    /**
     * Get available locales
     *
     * @return ArrayCollection|null
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales->isEmpty() ? null : $this->availableLocales;
    }

    /**
     * Set available locales
     *
     * @param ArrayCollection $availableLocales
     *
     * @return AbstractAttribute
     */
    public function setAvailableLocales(ArrayCollection $availableLocales)
    {
        $this->availableLocales = $availableLocales;

        return $this;
    }

    /**
     * Add family
     *
     * @param Family $family
     *
     * @return AbstractAttribute
     */
    public function addFamily(Family $family)
    {
        $this->families[] = $family;

        return $this;
    }

    /**
     * Remove family
     *
     * @param Family $family
     *
     * @return AbstractAttribute
     */
    public function removeFamily(Family $family)
    {
        $this->families->removeElement($family);

        return $this;
    }

    /**
     * Get families
     *
     * @return ArrayCollection|null
     */
    public function getFamilies()
    {
        return $this->families->isEmpty() ? null : $this->families;
    }

    /**
     * Set families
     *
     * @param ArrayCollection $families
     *
     * @return AbstractAttribute
     */
    public function setFamilies(ArrayCollection $families)
    {
        $this->families = $families;

        return $this;
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
     * @return AbstractAttribute
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
     * @return AbstractAttribute
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
     * @return AbstractAttribute
     */
    public function setValidationRegexp($validationRegexp)
    {
        $this->validationRegexp = $validationRegexp;

        return $this;
    }

    /**
     * Predicate for wysiwygEnabled property
     *
     * @return boolean $wysiwygEnabled
     */
    public function isWysiwygEnabled()
    {
        return $this->wysiwygEnabled;
    }

    /**
     * Set wysiwygEnabled
     *
     * @param boolean $wysiwygEnabled
     *
     * @return AbstractAttribute
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
     * @return AbstractAttribute
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
     * @return AbstractAttribute
     */
    public function setNumberMax($numberMax)
    {
        $this->numberMax = $numberMax;

        return $this;
    }

    /**
     * Predicate for decimalsAllowed property
     *
     * @return boolean $decimalsAllowed
     */
    public function isDecimalsAllowed()
    {
        return $this->decimalsAllowed;
    }

    /**
     * Set decimalsAllowed
     *
     * @param boolean $decimalsAllowed
     *
     * @return AbstractAttribute
     */
    public function setDecimalsAllowed($decimalsAllowed)
    {
        $this->decimalsAllowed = $decimalsAllowed;

        return $this;
    }

    /**
     * Predicate for negativeAllowed property
     *
     * @return boolean $negativeAllowed
     */
    public function isNegativeAllowed()
    {
        return $this->negativeAllowed;
    }

    /**
     * Set negativeAllowed
     *
     * @param boolean $negativeAllowed
     *
     * @return AbstractAttribute
     */
    public function setNegativeAllowed($negativeAllowed)
    {
        $this->negativeAllowed = $negativeAllowed;

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
     * @return AbstractAttribute
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
     * @return AbstractAttribute
     */
    public function setDateMax($dateMax)
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    /**
     * Get metricFamily
     *
     * @return string $metricFamily
     */
    public function getMetricFamily()
    {
        return $this->metricFamily;
    }

    /**
     * Set metricFamily
     *
     * @param string $metricFamily
     *
     * @return AbstractAttribute
     */
    public function setMetricFamily($metricFamily)
    {
        $this->metricFamily = $metricFamily;

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
     * @return AbstractAttribute
     */
    public function setDefaultMetricUnit($defaultMetricUnit)
    {
        $this->defaultMetricUnit = $defaultMetricUnit;

        return $this;
    }

    /**
     * Get maxFileSize
     *
     * @return decimal $maxFileSize
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * Set maxFileSize
     *
     * @param decimal $maxFileSize
     *
     * @return AbstractAttribute
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Get allowedExtensions
     *
     * @return array $allowedExtensions
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions ? array_map('trim', explode(',', $this->allowedExtensions)) : array();
    }

    /**
     * Set allowedExtensions
     *
     * @param string $allowedExtensions
     *
     * @return AbstractAttribute
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $allowedExtensions = explode(',', strtolower($allowedExtensions));
        $allowedExtensions = array_unique(array_map('trim', $allowedExtensions));
        $this->allowedExtensions = implode(',', $allowedExtensions);

        return $this;
    }

    /**
     * Returns the minimum input length for singlechoice and multichoice types
     *
     * @return int
     */
    public function getMinimumInputLength()
    {
        return $this->minimumInputLength;
    }

    /**
     * Sets the minimum input length for singlechoice and multichoice types
     *
     * @param integer $minimumInputLength
     *
     * @return AbstractAttribute
     */
    public function setMinimumInputLength($minimumInputLength)
    {
        $this->minimumInputLength = $minimumInputLength;

        return $this;
    }

        /**
     * Get sortOrder
     *
     * @return number
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set sortOrder
     *
     * @param number $sortOrder
     *
     * @return AbstractAttribute
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Set all parameters with associative array
     *
     * @param array $parameters
     *
     * @return AbstractAttribute
     *
     * @throws \Exception
     */
    public function setParameters($parameters)
    {
        foreach ($parameters as $code => $value) {
            $method = 'set'.ucfirst($code);
            if (!method_exists($this, $method)) {
                throw new \Exception(sprintf('The parameter "%s" does not exist.', $code));
            }
            $this->$method($value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation($locale = null)
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (!$locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation      = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(AbstractTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(AbstractTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return 'Pim\Bundle\CatalogBundle\Entity\AttributeTranslation';
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return string
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeType($type)
    {
        $this->attributeType = $type;
        if ($this->attributeType === 'pim_catalog_identifier') {
            $this->required = true;
        }

        return $this;
    }
}
