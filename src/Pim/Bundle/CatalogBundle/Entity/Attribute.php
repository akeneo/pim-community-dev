<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

/**
 * Custom properties for an attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Assert\GroupSequenceProvider
 *
 * @ExclusionPolicy("all")
 */
class Attribute extends AbstractEntityAttribute implements
    TranslatableInterface,
    GroupSequenceProviderInterface,
    ReferableInterface
{
    /**
     * Overrided to change target entity name
     *
     * @var ArrayCollection $options
     */
    protected $options;

    protected $sortOrder = 0;

    /**
     * @var AttributeGroup
     */
    protected $group;

    /**
     * @var boolean $useableAsGridColumn
     */
    protected $useableAsGridColumn;

    /**
     * @var boolean $useableAsGridFilter
     */
    protected $useableAsGridFilter;

    /**
     * @var $availableLocales ArrayCollection
     */
    protected $availableLocales;

    /**
     * @var $families ArrayCollection
     */
    protected $families;

    /**
     * @var integer $maxCharacters
     */
    protected $maxCharacters;

    /**
     * @var string $validationRule
     */
    protected $validationRule;

    /**
     * @var string $validationRegexp
     */
    protected $validationRegexp;

    /**
     * @var boolean $wysiwygEnabled
     */
    protected $wysiwygEnabled;

    /**
     * @var decimal $numberMin
     */
    protected $numberMin;

    /**
     * @var decimal $numberMax
     */
    protected $numberMax;

    /**
     * @var boolean $decimalsAllowed
     */
    protected $decimalsAllowed;

    /**
     * @var boolean $negativeAllowed
     */
    protected $negativeAllowed;

    /**
     * @var datetime $dateMin
     */
    protected $dateMin;

    /**
     * @var datetime $dateMax
     */
    protected $dateMax;

    /**
     * @var string $metricFamily
     */
    protected $metricFamily;

    /**
     * @var string $defaultMetricUnit
     */
    protected $defaultMetricUnit;

    /**
     * @var decimal $maxFileSize
     * expressed in MB so decimal is needed for values < 1 MB
     */
    protected $maxFileSize;

    /**
     * @var array $allowedExtensions
     */
    protected $allowedExtensions;

    /**
     * @var integer
     */
    protected $minimumInputLength = 0;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale;

    /**
     * @var ArrayCollection $translations
     */
    protected $translations;

    protected $required;

    protected $unique;

    protected $defaultValue;

    protected $searchable;

    protected $localizable;

    protected $scopable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options             = new ArrayCollection();
        $this->required            = false;
        $this->unique              = false;
        $this->defaultValue        = null;
        $this->searchable          = false;
        $this->localizable         = false;
        $this->scopable            = false;
        $this->useableAsGridColumn = false;
        $this->useableAsGridFilter = false;
        $this->availableLocales    = new ArrayCollection();
        $this->families            = new ArrayCollection();
        $this->translations        = new ArrayCollection();
        $this->validationRule      = null;
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
        if (is_null($this->defaultValue) && $this->getDefaultOptions()->isEmpty()) {
            return null;
        }

        switch ($this->getAttributeType()) {
            case 'pim_catalog_simpleselect':
                $default = $this->getDefaultOptions()->first();
                break;
            case 'pim_catalog_multiselect':
                $default = $this->getDefaultOptions();
                break;
            case 'pim_catalog_date':
                $default = new \DateTime();
                $default->setTimestamp((int) $this->defaultValue);
                break;
            case 'pim_catalog_boolean':
                $default = (bool) $this->defaultValue;
                break;
            default:
                $default = $this->defaultValue;
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
            switch ($this->getAttributeType()) {
                case 'pim_catalog_date':
                    $this->defaultValue = $defaultValue->format('U');
                    break;
                case 'pim_catalog_boolean':
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
     * Get virtual group
     * Returns a group named 'Other' if entity doesn't belong to a group
     *
     * @return AttributeGroup
     */
    public function getVirtualGroup()
    {
        if ($this->group) {
            return $this->group;
        }

        $group = new AttributeGroup();
        $group->setId(0);
        $group->setCode(AttributeGroup::DEFAULT_GROUP_CODE);
        $group->setSortOrder(-1);

        return $group;
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
}
