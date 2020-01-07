<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Abstract product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttribute implements AttributeInterface
{
    /** @var int|string */
    protected $id;

    /**
     * Attribute code
     *
     * @var string
     */
    protected $code;

    /**
     * Attribute label
     *
     * @var string
     */
    protected $label;

    /**
     * Entity type (FQCN)
     *
     * @var string
     */
    protected $entityType;

    /**
     * Attribute type
     *
     * @var string
     */
    protected $type;

    /**
     * Kind of field to store values
     *
     * @var string
     */
    protected $backendType;

    /** @var \DateTime */
    protected $created;

    /** @var \DateTime */
    protected $updated;

    /**
     * Is attribute is required
     *
     * @var bool
     */
    protected $required;

    /**
     * Is attribute value is required
     *
     * @var bool
     */
    protected $unique;

    /** @var bool */
    protected $localizable;

    /** @var bool */
    protected $scopable;

    /** @var array */
    protected $properties;

    /** @var ArrayCollection */
    protected $options;

    /** @var int */
    protected $sortOrder = 0;

    /** @var AttributeGroupInterface $group */
    protected $group;

    /** @var bool */
    protected $useableAsGridFilter;

    /** @var ArrayCollection */
    protected $availableLocales;

    /** @var ArrayCollection */
    protected $families;

    /** @var int */
    protected $maxCharacters;

    /** @var string */
    protected $validationRule;

    /** @var string */
    protected $validationRegexp;

    /** @var bool */
    protected $wysiwygEnabled;

    /** @var float */
    protected $numberMin;

    /** @var float */
    protected $numberMax;

    /** @var bool */
    protected $decimalsAllowed;

    /** @var bool */
    protected $negativeAllowed;

    /** @var \DateTime */
    protected $dateMin;

    /** @var \DateTime */
    protected $dateMax;

    /** @var string */
    protected $metricFamily;

    /** @var string */
    protected $defaultMetricUnit;

    /**
     * @var float expressed in MB so decimal is needed for values < 1 MB
     */
    protected $maxFileSize;

    /** @var array */
    protected $allowedExtensions;

    /** @var int */
    protected $minimumInputLength;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var ArrayCollection */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->required = false;
        $this->unique = false;
        $this->localizable = false;
        $this->scopable = false;
        $this->useableAsGridFilter = false;
        $this->availableLocales = new ArrayCollection();
        $this->families = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->validationRule = null;
        $this->properties = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBackendType($type)
    {
        $this->backendType = $type;

        return $this;
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
    public function getAttributeType()
    {
        return $this->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * {@inheritdoc}
     */
    public function setUnique($unique)
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique()
    {
        return $this->unique;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocalizable($localizable)
    {
        $this->localizable = $localizable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizable()
    {
        return $this->localizable;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopable($scopable)
    {
        $this->scopable = $scopable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isScopable()
    {
        return $this->scopable;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(AttributeOptionInterface $option)
    {
        $this->options[] = $option;
        $option->setAttribute($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption(AttributeOptionInterface $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($property)
    {
        return isset($this->properties[$property]) ? $this->properties[$property] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['Attribute', $this->getType()];

        if ($this->isUnique()) {
            $groups[] = 'unique';
        }
        if ($this->isScopable()) {
            $groups[] = 'scopable';
        }
        if ($this->isLocalizable()) {
            $groups[] = 'localizable';
        }
        if ($rule = $this->getValidationRule()) {
            $groups[] = $rule;
        }

        return $groups;
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
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(AttributeGroupInterface $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isUseableAsGridFilter()
    {
        return $this->useableAsGridFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function setUseableAsGridFilter($useableAsGridFilter)
    {
        $this->useableAsGridFilter = $useableAsGridFilter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAvailableLocale(LocaleInterface $availableLocale)
    {
        $this->availableLocales[] = $availableLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAvailableLocale(LocaleInterface $availableLocale)
    {
        $this->availableLocales->removeElement($availableLocale);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleSpecificCodes()
    {
        return $this->getAvailableLocaleCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocaleCodes()
    {
        $codes = [];
        foreach ($this->getAvailableLocales() as $locale) {
            $codes[] = $locale->getCode();
        }

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocaleSpecific(LocaleInterface $locale)
    {
        return in_array($locale->getCode(), $this->getLocaleSpecificCodes());
    }

    /**
     * {@inheritdoc}
     */
    public function addFamily(FamilyInterface $family)
    {
        $this->families[] = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFamily(FamilyInterface $family)
    {
        $this->families->removeElement($family);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilies()
    {
        return $this->families->isEmpty() ? null : $this->families;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamilies(ArrayCollection $families)
    {
        $this->families = $families;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxCharacters()
    {
        return $this->maxCharacters;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxCharacters($maxCharacters)
    {
        $this->maxCharacters = $maxCharacters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRule()
    {
        return $this->validationRule;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidationRule($validationRule)
    {
        $this->validationRule = $validationRule;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRegexp()
    {
        return $this->validationRegexp;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidationRegexp($validationRegexp)
    {
        $this->validationRegexp = $validationRegexp;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isWysiwygEnabled()
    {
        return $this->wysiwygEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setWysiwygEnabled($wysiwygEnabled)
    {
        $this->wysiwygEnabled = $wysiwygEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberMin()
    {
        return $this->numberMin;
    }

    /**
     * {@inheritdoc}
     */
    public function setNumberMin($numberMin)
    {
        $this->numberMin = $numberMin;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberMax()
    {
        return $this->numberMax;
    }

    /**
     * {@inheritdoc}
     */
    public function setNumberMax($numberMax)
    {
        $this->numberMax = $numberMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecimalsAllowed()
    {
        return $this->decimalsAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecimalsAllowed($decimalsAllowed)
    {
        $this->decimalsAllowed = $decimalsAllowed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isNegativeAllowed()
    {
        return $this->negativeAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function setNegativeAllowed($negativeAllowed)
    {
        $this->negativeAllowed = $negativeAllowed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateMin()
    {
        return $this->dateMin;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateMin($dateMin)
    {
        $this->dateMin = $dateMin;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateMax()
    {
        return $this->dateMax;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateMax($dateMax)
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetricFamily()
    {
        return $this->metricFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetricFamily($metricFamily)
    {
        $this->metricFamily = $metricFamily;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultMetricUnit()
    {
        return $this->defaultMetricUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultMetricUnit($defaultMetricUnit)
    {
        $this->defaultMetricUnit = $defaultMetricUnit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions ? array_map('trim', explode(',', $this->allowedExtensions)) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $allowedExtensions = explode(',', strtolower($allowedExtensions));
        $allowedExtensions = array_unique(array_map('trim', $allowedExtensions));
        $this->allowedExtensions = implode(',', $allowedExtensions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinimumInputLength()
    {
        return $this->minimumInputLength;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinimumInputLength($minimumInputLength)
    {
        $this->minimumInputLength = $minimumInputLength;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * {@inheritdoc}
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
    public function getTranslation(?string $locale = null)
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (null === $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation = new $translationClass();
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
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return AttributeTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
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
        return $this->setType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
        if (AttributeTypes::IDENTIFIER === $this->type) {
            $this->required = true;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocaleSpecific()
    {
        if ($this->availableLocales->isEmpty()) {
            return false;
        } else {
            return !empty($this->availableLocales);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceDataName()
    {
        if (!$this->isBackendTypeReferenceData()) {
            return null;
        }

        return $this->getProperty('reference_data_name');
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceDataName($name)
    {
        $this->setProperty('reference_data_name', $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isBackendTypeReferenceData()
    {
        return in_array($this->getBackendType(), [
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION,
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS
        ]);
    }
}
