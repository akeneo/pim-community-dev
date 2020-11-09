<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
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
    public function setId($id): AttributeInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): AttributeInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityType(string $entityType): AttributeInterface
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreated(\DateTime $created): TimestampableInterface
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdated(\DateTime $updated): TimestampableInterface
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBackendType(string $type): AttributeInterface
    {
        $this->backendType = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType(): string
    {
        return $this->backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeType(): string
    {
        return $this->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequired(bool $required): AttributeInterface
    {
        $this->required = $required;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * {@inheritdoc}
     */
    public function setUnique(bool $unique): AttributeInterface
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocalizable(bool $localizable): AttributeInterface
    {
        $this->localizable = $localizable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizable(): bool
    {
        return $this->localizable;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopable(bool $scopable): AttributeInterface
    {
        $this->scopable = $scopable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isScopable(): bool
    {
        return $this->scopable;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(AttributeOptionInterface $option): AttributeInterface
    {
        $this->options[] = $option;
        $option->setAttribute($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption(AttributeOptionInterface $option): AttributeInterface
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): \ArrayAccess
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperties(array $properties): AttributeInterface
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty(string $property)
    {
        return isset($this->properties[$property]) ? $this->properties[$property] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty(string $property, $value): AttributeInterface
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence(): array
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
        if (($rule = $this->getValidationRule()) !== '') {
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
    public function getGroup(): \Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(AttributeGroupInterface $group = null): AttributeInterface
    {
        $this->group = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isUseableAsGridFilter(): bool
    {
        return $this->useableAsGridFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function setUseableAsGridFilter(bool $useableAsGridFilter): AttributeInterface
    {
        $this->useableAsGridFilter = $useableAsGridFilter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAvailableLocale(LocaleInterface $availableLocale): AttributeInterface
    {
        $this->availableLocales[] = $availableLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAvailableLocale(LocaleInterface $availableLocale): AttributeInterface
    {
        $this->availableLocales->removeElement($availableLocale);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleSpecificCodes(): array
    {
        return $this->getAvailableLocaleCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocaleCodes(): array
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
    public function hasLocaleSpecific(LocaleInterface $locale): bool
    {
        return in_array($locale->getCode(), $this->getLocaleSpecificCodes());
    }

    /**
     * {@inheritdoc}
     */
    public function addFamily(FamilyInterface $family): AttributeInterface
    {
        $this->families[] = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFamily(FamilyInterface $family): AttributeInterface
    {
        $this->families->removeElement($family);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilies(): ?\Doctrine\Common\Collections\ArrayCollection
    {
        return $this->families->isEmpty() ? null : $this->families;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamilies(ArrayCollection $families): AttributeInterface
    {
        $this->families = $families;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxCharacters(): int
    {
        return $this->maxCharacters;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxCharacters(int $maxCharacters): AttributeInterface
    {
        $this->maxCharacters = $maxCharacters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRule(): string
    {
        return $this->validationRule;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidationRule(string $validationRule): AttributeInterface
    {
        $this->validationRule = $validationRule;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRegexp(): string
    {
        return $this->validationRegexp;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidationRegexp(string $validationRegexp): AttributeInterface
    {
        $this->validationRegexp = $validationRegexp;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isWysiwygEnabled(): bool
    {
        return $this->wysiwygEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setWysiwygEnabled(bool $wysiwygEnabled): AttributeInterface
    {
        $this->wysiwygEnabled = $wysiwygEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberMin(): float
    {
        return $this->numberMin;
    }

    /**
     * {@inheritdoc}
     */
    public function setNumberMin($numberMin): AttributeInterface
    {
        $this->numberMin = $numberMin;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberMax(): float
    {
        return $this->numberMax;
    }

    /**
     * {@inheritdoc}
     */
    public function setNumberMax($numberMax): AttributeInterface
    {
        $this->numberMax = $numberMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecimalsAllowed(): bool
    {
        return $this->decimalsAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecimalsAllowed(bool $decimalsAllowed): AttributeInterface
    {
        $this->decimalsAllowed = $decimalsAllowed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isNegativeAllowed(): bool
    {
        return $this->negativeAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function setNegativeAllowed(bool $negativeAllowed): AttributeInterface
    {
        $this->negativeAllowed = $negativeAllowed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateMin(): \DateTime
    {
        return $this->dateMin;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateMin(\DateTime $dateMin): AttributeInterface
    {
        $this->dateMin = $dateMin;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateMax(): \DateTime
    {
        return $this->dateMax;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateMax(\DateTime $dateMax): AttributeInterface
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetricFamily(): string
    {
        return $this->metricFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetricFamily(string $metricFamily): AttributeInterface
    {
        $this->metricFamily = $metricFamily;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultMetricUnit(): string
    {
        return $this->defaultMetricUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultMetricUnit(string $defaultMetricUnit): AttributeInterface
    {
        $this->defaultMetricUnit = $defaultMetricUnit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxFileSize(): float
    {
        return $this->maxFileSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxFileSize(float $maxFileSize): AttributeInterface
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions ? array_map('trim', explode(',', $this->allowedExtensions)) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedExtensions(string $allowedExtensions): AttributeInterface
    {
        $allowedExtensions = explode(',', strtolower($allowedExtensions));
        $allowedExtensions = array_unique(array_map('trim', $allowedExtensions));
        $this->allowedExtensions = implode(',', $allowedExtensions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinimumInputLength(): int
    {
        return $this->minimumInputLength;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinimumInputLength(int $minimumInputLength): AttributeInterface
    {
        $this->minimumInputLength = $minimumInputLength;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder): AttributeInterface
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters): AttributeInterface
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
    public function setLocale(?string $locale): TranslatableInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation(?string $locale = null): AbstractTranslation
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
    public function getTranslations(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation): TranslatableInterface
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN(): string
    {
        return AttributeTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        $translated = ($this->getTranslation() !== null) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label): string
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeType(string $type): AttributeInterface
    {
        return $this->setType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function setType(string $type): AttributeInterface
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
    public function isLocaleSpecific(): bool
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
    public function getReferenceDataName(): string
    {
        if (!$this->isBackendTypeReferenceData()) {
            return null;
        }

        return $this->getProperty('reference_data_name');
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceDataName(string $name): self
    {
        $this->setProperty('reference_data_name', $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isBackendTypeReferenceData(): bool
    {
        return in_array($this->getBackendType(), [
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION,
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS
        ]);
    }
}
