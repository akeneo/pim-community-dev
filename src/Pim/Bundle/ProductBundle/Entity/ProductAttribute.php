<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;
use Pim\Bundle\ConfigBundle\Entity\Locale;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;

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
 * @Assert\GroupSequenceProvider
 */
class ProductAttribute extends AbstractEntityAttribute implements TranslatableInterface, GroupSequenceProviderInterface,
 VersionableInterface
{
    /**
     * @var integer $version
     *
     * @ORM\Column(name="version", type="integer")
     * @ORM\Version
     */
    protected $version;

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
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var string $variant
     *
     * @ORM\Column(name="variant", type="string", length=255, nullable=true)
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
     * @var $availableLocales ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ConfigBundle\Entity\Locale")
     * @ORM\JoinTable(name="pim_product_attribute_locale")
     */
    protected $availableLocales;

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
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * @ORM\Column(name="is_required", type="boolean")
     */
    protected $required;

    /**
     * @ORM\Column(name="is_unique", type="boolean")
     */
    protected $unique;

    /**
     * @ORM\Column(name="default_value", type="text", length=65532, nullable=true)
     */
    protected $defaultValue;

    /**
     * @ORM\Column(name="is_searchable", type="boolean")
     */
    protected $searchable;

    /**
     * @ORM\Column(name="is_translatable", type="boolean")
     */
    protected $translatable;

    /**
     * @ORM\Column(name="is_scopable", type="boolean")
     */
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
        $this->translatable        = false;
        $this->scopable            = false;
        $this->description         = '';
        $this->smart               = false;
        $this->variant             = false;
        $this->useableAsGridColumn = false;
        $this->useableAsGridFilter = false;
        $this->availableLocales    = new ArrayCollection();
        $this->translations        = new ArrayCollection();
    }

    /**
     * Get version
     *
     * @return string $version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Return the identifier-based validation group for validation of properties
     * @return array:string
     */
    public function getGroupSequence()
    {
        return array('Default', $this->getAttributeType());
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

        switch ($this->getBackendType()) {
            case 'option':
                return $this->getDefaultOptions()->isEmpty() ? null : $this->getDefaultOptions()->first();
            case 'options':
                return $this->getDefaultOptions();
            case 'date':
                $date = new \DateTime();
                $date->setTimestamp((int) $this->defaultValue);

                return $date;
            case 'boolean':
                return (bool) $this->defaultValue;
            default:
                return $this->defaultValue;
        }
    }

    /**
     * Set default value
     *
     * @param mixed $defaultValue
     *
     * @return ProductAttribute
     */
    public function setDefaultValue($defaultValue)
    {
        if (is_null($defaultValue)) {
            $this->defaultValue = null;

            return $this;
        } elseif ($defaultValue instanceof ArrayCollection && $defaultValue->isEmpty()) {
            $this->defaultOption = null;

            return $this;
        }

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
     * Predicate for smart property
     *
     * @return boolean $smart
     */
    public function isSmart()
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
     * @return ProductAttribute
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
     * Get virtual group
     * Returns a group named 'Other' if entity doesn't belong to a group
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function getVirtualGroup()
    {
        if ($this->group) {
            return $this->group;
        }

        $group = new AttributeGroup;
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
     * @return ProductAttribute
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
     * @return ProductAttribute
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
     * @return ProductAttribute
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
     * @return ProductAttribute
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
     * @return ProductAttribute
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
     * @return ProductAttribute
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
     * @return ProductAttribute
     */
    public function setNegativeAllowed($negativeAllowed)
    {
        $this->negativeAllowed = $negativeAllowed;

        return $this;
    }

    /**
     * Predicate for valueCreationAllowed property
     *
     * @return boolean $valueCreationAllowed
     */
    public function isValueCreationAllowed()
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
        return $this->allowedFileExtensions ? array_map('trim', explode(',', $this->allowedFileExtensions)) : array();
    }

    /**
     * Set allowedFileExtensions
     *
     * @param string $allowedFileExtensions
     *
     * @return ProductAttribute
     */
    public function setAllowedFileExtensions($allowedFileExtensions)
    {
        $this->allowedFileExtensions = $allowedFileExtensions;

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
     * @return ProductAttribute
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
     * @return ProductAttribute
     *
     * @throws \Exception
     */
    public function setParameters($parameters)
    {
        foreach ($parameters as $code => $value) {
            $method = 'set'.ucfirst($code);
            if (!method_exists($this, $method)) {
                throw new \Exception(sprintf('The parameter "%s" doesnt exists.', $code));
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
        return 'Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation';
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
     * @return array
     */
    public function getVersionedData()
    {
        $normalizer = new \Pim\Bundle\ImportExportBundle\Normalizer\AttributeNormalizer();
        $data = $normalizer->normalize($this);

        /*
        $data = array(
            'code' => $this->getCode(),
        );

        foreach ($this->getTranslations() as $translation) {
            $data['label_'.$translation->getLocale()]= $translation->getLabel();
        }*/

        return $data;
    }
}
