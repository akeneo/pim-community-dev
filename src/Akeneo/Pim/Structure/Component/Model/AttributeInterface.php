<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Product attribute interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeInterface extends
    TimestampableInterface,
    TranslatableInterface,
    GroupSequenceProviderInterface,
    ReferableInterface,
    VersionableInterface
{
    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set required
     *
     * @param bool $required
     */
    public function setRequired(bool $required): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Is unique
     *
     * @return bool $unique
     */
    public function isUnique(): bool;

    /**
     * Set numberMin
     *
     * @param mixed $numberMin
     */
    public function setNumberMin($numberMin): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get properties
     */
    public function getProperties(): array;

    /**
     * Get backend type
     */
    public function getBackendType(): string;

    /**
     * Set id
     *
     * @param int|string $id
     */
    public function setId($id): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Is scopable
     *
     * @return bool $scopable
     */
    public function isScopable(): bool;

    /**
     * Set localizable
     *
     * @param bool $localizable
     */
    public function setLocalizable(bool $localizable): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get entity type
     */
    public function getEntityType(): string;

    /**
     * Add option
     *
     * @param AttributeOptionInterface $option
     */
    public function addOption(AttributeOptionInterface $option): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get available locale codes
     *
     * @deprecated Will be removed in 1.8. Please use getAvailableLocaleCodes() instead.
     */
    public function getLocaleSpecificCodes(): array;

    /**
     * Get available locale
     */
    public function getAvailableLocales(): array;

    /**
     * Get available locale codes
     */
    public function getAvailableLocaleCodes(): array;

    /**
     * Test if the attribute have the given locale specific available
     *
     * @param LocaleInterface $locale
     */
    public function hasLocaleSpecific(LocaleInterface $locale): bool;

    /**
     * Set entity type
     *
     * @param string $entityType
     */
    public function setEntityType(string $entityType): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get maxFileSize
     *
     * @return float $maxFileSize
     */
    public function getMaxFileSize(): float;

    /**
     * Set wysiwygEnabled
     *
     * @param bool $wysiwygEnabled
     */
    public function setWysiwygEnabled(bool $wysiwygEnabled): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get metricFamily
     *
     * @return string $metricFamily
     */
    public function getMetricFamily(): string;

    /**
     * Set backend type
     *
     * @param string $type
     */
    public function setBackendType(string $type): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get defaultMetricUnit
     *
     * @return string $defaultMetricUnit
     */
    public function getDefaultMetricUnit(): string;

    /**
     * Get numberMax
     *
     * @return mixed $numberMax
     */
    public function getNumberMax();

    /**
     * Get families
     */
    public function getFamilies(): ?\Doctrine\Common\Collections\ArrayCollection;

    /**
     * Get label
     */
    public function getLabel(): string;

    /**
     * Get options
     */
    public function getOptions(): \ArrayAccess;

    /**
     * Predicate for negativeAllowed property
     *
     * @return bool $negativeAllowed
     */
    public function isNegativeAllowed(): bool;

    /**
     * Returns the minimum input length for singlechoice and multichoice types
     */
    public function getMinimumInputLength(): int;

    /**
     * Set a property
     *
     * @param string $property
     * @param mixed  $value
     */
    public function setProperty(string $property, $value): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set group
     *
     * @param AttributeGroupInterface $group
     */
    public function setGroup(AttributeGroupInterface $group = null): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get allowedExtensions
     *
     * @return array $allowedExtensions
     */
    public function getAllowedExtensions(): array;

    /**
     * Set allowedExtensions
     *
     * @param string $allowedExtensions
     */
    public function setAllowedExtensions(string $allowedExtensions): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get a property
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty(string $property);

    /**
     * Set negativeAllowed
     *
     * @param bool $negativeAllowed
     */
    public function setNegativeAllowed(bool $negativeAllowed): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Is localizable
     *
     * @return bool $localizable
     */
    public function isLocalizable(): bool;

    /**
     * Add available locale
     *
     * @param LocaleInterface $availableLocale
     */
    public function addAvailableLocale(LocaleInterface $availableLocale): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get code
     */
    public function getCode(): string;

    /**
     * Predicate for useableAsGridFilter property
     *
     * @return bool $useableAsGridFilter
     */
    public function isUseableAsGridFilter(): bool;

    /**
     * Set families
     *
     * @param ArrayCollection $families
     */
    public function setFamilies(ArrayCollection $families): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set Validation regexp
     *
     * @param string $validationRegexp
     */
    public function setValidationRegexp(string $validationRegexp): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get numberMin
     *
     * @return mixed $numberMin
     */
    public function getNumberMin();

    /**
     * Add family
     *
     * @param FamilyInterface $family
     */
    public function addFamily(FamilyInterface $family): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set Validation rule
     *
     * @param string $validationRule
     */
    public function setValidationRule(string $validationRule): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set dateMin
     *
     * @param \DateTime $dateMin
     */
    public function setDateMin(\DateTime $dateMin): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set attribute type
     *
     * @param string $type
     *
     *
     * @deprecated Will be removed in 1.8. Please use setType() instead.
     */
    public function setAttributeType(string $type): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set attribute type
     *
     * @param string $type
     *
     * @see Pim\Component\Catalog\AttributeTypes
     */
    public function setType(string $type): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set dateMax
     *
     * @param \DateTime $dateMax
     */
    public function setDateMax(\DateTime $dateMax): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set maxFileSize
     *
     * @param float $maxFileSize
     */
    public function setMaxFileSize(float $maxFileSize): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get group
     */
    public function getGroup(): \Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): string;

    /**
     * Get attribute type
     *
     *
     * @deprecated Will be removed in 1.8. Please use getType() instead.
     */
    public function getAttributeType(): string;

    /**
     * Get attribute type
     *
     * @see Pim\Component\Catalog\AttributeTypes
     */
    public function getType(): string;

    /**
     * Predicate for wysiwygEnabled property
     *
     * @return bool $wysiwygEnabled
     */
    public function isWysiwygEnabled(): bool;

    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Get Validation rule
     *
     * @return string $validationRule
     */
    public function getValidationRule(): string;

    /**
     * Set properties
     *
     * @param array $properties
     */
    public function setProperties(array $properties): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Is required
     *
     * @return bool $required
     */
    public function isRequired(): bool;

    /**
     * Set all parameters with associative array
     *
     * @param array $parameters
     *
     * @throws \Exception
     */
    public function setParameters(array $parameters): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set Max characters
     *
     * @param int $maxCharacters
     */
    public function setMaxCharacters(int $maxCharacters): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set unique
     *
     * @param bool $unique
     */
    public function setUnique(bool $unique): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get dateMin
     *
     * @return \DateTime $dateMin
     */
    public function getDateMin(): \DateTime;

    /**
     * Remove family
     *
     * @param FamilyInterface $family
     */
    public function removeFamily(FamilyInterface $family): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set numberMax
     *
     * @param mixed $numberMax
     */
    public function setNumberMax($numberMax): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set useableAsGridFilter
     *
     * @param bool $useableAsGridFilter
     */
    public function setUseableAsGridFilter(bool $useableAsGridFilter): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get dateMax
     *
     * @return \DateTime $dateMax
     */
    public function getDateMax(): \DateTime;

    /**
     * Sets the minimum input length for singlechoice and multichoice types
     *
     * @param int $minimumInputLength
     */
    public function setMinimumInputLength(int $minimumInputLength): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set sortOrder
     *
     * @param number $sortOrder
     */
    public function setSortOrder($sortOrder): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Predicate for decimalsAllowed property
     *
     * @return bool $decimalsAllowed
     */
    public function isDecimalsAllowed(): bool;

    /**
     * Set defaultMetricUnit
     *
     * @param string $defaultMetricUnit
     */
    public function setDefaultMetricUnit(string $defaultMetricUnit): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Remove option
     *
     * @param AttributeOptionInterface $option
     */
    public function removeOption(AttributeOptionInterface $option): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get Max characters
     *
     * @return int $maxCharacters
     */
    public function getMaxCharacters(): int;

    /**
     * Remove available locale
     *
     * @param LocaleInterface $availableLocale
     */
    public function removeAvailableLocale(LocaleInterface $availableLocale): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set scopable
     *
     * @param bool $scopable
     */
    public function setScopable(bool $scopable): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get sortOrder
     */
    public function getSortOrder(): int;

    /**
     * Get Validation regexp
     *
     * @return string $validationRegexp
     */
    public function getValidationRegexp(): string;

    /**
     * Set decimalsAllowed
     *
     * @param bool $decimalsAllowed
     */
    public function setDecimalsAllowed(bool $decimalsAllowed): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set metricFamily
     *
     * @param string $metricFamily
     */
    public function setMetricFamily(string $metricFamily): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Check if attribute is locale specific
     */
    public function isLocaleSpecific(): bool;

    public function getReferenceDataName(): string;

    /**
     * @param string $name
     */
    public function setReferenceDataName(string $name);

    public function isBackendTypeReferenceData(): bool;
}
