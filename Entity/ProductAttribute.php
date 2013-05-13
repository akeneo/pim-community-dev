<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;
use Pim\Bundle\ConfigBundle\Entity\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Custom properties for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(
 *     name="pim_product_attribute", indexes={@ORM\Index(name="searchcode_idx", columns={"code"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="searchunique_idx", columns={"code", "entity_type"})}
 * )
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\ProductAttributeRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\TranslationEntity(class="Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation")
 * @UniqueEntity("code")
 */
class ProductAttribute extends AbstractEntityAttribute implements Translatable
{
    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255)
     * @Gedmo\Translatable
     */
    protected $label;

    /**
     * Overrided to change target entity name
     *
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\AttributeOption",
     *     mappedBy="attribute",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $options;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder = 0;

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
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="SET NULL")
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
     * @ORM\JoinTable(name="pim_product_attribute_language")
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
     * @var boolean $decimalsAllowed
     *
     * @ORM\Column(name="decimals_allowed", type="boolean", nullable=true)
     */
    protected $decimalsAllowed;

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
     * @var string $metricFamily
     *
     * @ORM\Column(name="metric_family", type="string", length=30, nullable=true)
     */
    protected $metricFamily;

    /**
     * @var string $defaultMetricUnit
     *
     * @ORM\Column(name="default_metric_unit", type="string", length=30, nullable=true)
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
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     *
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="ProductAttributeTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist", "remove"}
     * )
     */
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
        $this->searchable          = false;
        $this->translatable        = false;
        $this->scopable            = false;
        $this->description         = '';
        $this->smart               = false;
        $this->variant             = false;
        $this->useableAsGridColumn = false;
        $this->useableAsGridFilter = false;
        $this->availableLanguages  = new ArrayCollection();
        $this->translations        = new ArrayCollection();
    }

    /**
     * Convert defaultValue to UNIX timestamp if it is a DateTime object
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return null
     */
    public function convertDefaultValueToTimestamp()
    {
        if ($this->getDefaultValue() instanceof \DateTime) {
            $this->setDefaultValue($this->getDefaultValue()->format('U'));
        }
    }

    /**
     * Convert defaultValue to DateTime if attribute type is date
     *
     * @ORM\PostLoad
     *
     * @return null
     */
    public function convertDefaultValueToDatetime()
    {
        if ($this->getDefaultValue()) {
            if (strpos($this->getAttributeType(), 'DateType') !== false) {
                $date = new \DateTime();
                $date->setTimestamp(intval($this->getDefaultValue()));

                $this->setDefaultValue($date);
            }
        }
    }

    /**
     * Convert defaultValue to integer if attribute type is boolean
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return null
     */
    public function convertDefaultValueToInteger()
    {
        if ($this->getDefaultValue() !== null) {
            if (strpos($this->getAttributeType(), 'BooleanType') !== false) {
                $this->setDefaultValue((int) $this->getDefaultValue());
            }
        }
    }

    /**
     * Convert defaultValue to boolean if attribute type is boolean
     *
     * @ORM\PostLoad
     *
     * @return null
     */
    public function convertDefaultValueToBoolean()
    {
        if ($this->getDefaultValue() !== null) {
            if (strpos($this->getAttributeType(), 'BooleanType') !== false) {
                $this->setDefaultValue((bool) $this->getDefaultValue());
            }
        }
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->label;
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
        if ($this->group) {
            return $this->group;
        }

        $group = new AttributeGroup;
        $group->setName('Other');
        $group->setSortOrder(-1);

        return $group;
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
     * Get decimalsAllowed
     *
     * @return boolean $decimalsAllowed
     */
    public function getDecimalsAllowed()
    {
        return $this->decimalsAllowed;
    }

    /**
     * Set decimalsAllowed
     *
     * @param boolean $decimalsAllowed
     *
     * @return ProductAttribute
     */
    public function setDecimalsAllowed($decimalsAllowed)
    {
        $this->decimalsAllowed = $decimalsAllowed;

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
     * @return ProductAttribute
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
        $this->allowedFileExtensions = is_array($allowedFileExtensions)
            ? implode(',', $allowedFileExtensions) : $allowedFileExtensions;

        return $this;
    }

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return ProductAttribute
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get translations
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Add translation
     *
     * @param ProductAttributeTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    public function addTranslation(ProductAttributeTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * Remove translation
     *
     * @param ProductAttributeTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    public function removeTranslation(ProductAttributeTranslation $translation)
    {
        $this->translations->removeElement($translation);

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
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
