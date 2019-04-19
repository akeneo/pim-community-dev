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
     *
     * @return AttributeInterface
     */
    public function setCode($code);

    /**
     * Set required
     *
     * @param bool $required
     *
     * @return AttributeInterface
     */
    public function setRequired($required);

    /**
     * Is unique
     *
     * @return bool $unique
     */
    public function isUnique();

    /**
     * Set numberMin
     *
     * @param mixed $numberMin
     *
     * @return AttributeInterface
     */
    public function setNumberMin($numberMin);

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties();

    /**
     * Get backend type
     *
     * @return string
     */
    public function getBackendType();

    /**
     * Set id
     *
     * @param int|string $id
     *
     * @return AttributeInterface
     */
    public function setId($id);

    /**
     * Is scopable
     *
     * @return bool $scopable
     */
    public function isScopable();

    /**
     * Set localizable
     *
     * @param bool $localizable
     *
     * @return AttributeInterface
     */
    public function setLocalizable($localizable);

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Add option
     *
     * @param AttributeOptionInterface $option
     *
     * @return AttributeInterface
     */
    public function addOption(AttributeOptionInterface $option);

    /**
     * Get available locale codes
     *
     * @deprecated Will be removed in 1.8. Please use getAvailableLocaleCodes() instead.
     *
     * @return array
     */
    public function getLocaleSpecificCodes();

    /**
     * Get available locale
     *
     * @return array
     */
    public function getAvailableLocales();

    /**
     * Get available locale codes
     *
     * @return array
     */
    public function getAvailableLocaleCodes();

    /**
     * Test if the attribute have the given locale specific available
     *
     * @param LocaleInterface $locale
     *
     * @return bool
     */
    public function hasLocaleSpecific(LocaleInterface $locale);

    /**
     * Set entity type
     *
     * @param string $entityType
     *
     * @return AttributeInterface
     */
    public function setEntityType($entityType);

    /**
     * Get maxFileSize
     *
     * @return float $maxFileSize
     */
    public function getMaxFileSize();

    /**
     * Set wysiwygEnabled
     *
     * @param bool $wysiwygEnabled
     *
     * @return AttributeInterface
     */
    public function setWysiwygEnabled($wysiwygEnabled);

    /**
     * Get metricFamily
     *
     * @return string $metricFamily
     */
    public function getMetricFamily();

    /**
     * Set backend type
     *
     * @param string $type
     *
     * @return AttributeInterface
     */
    public function setBackendType($type);

    /**
     * Get defaultMetricUnit
     *
     * @return string $defaultMetricUnit
     */
    public function getDefaultMetricUnit();

    /**
     * Get numberMax
     *
     * @return mixed $numberMax
     */
    public function getNumberMax();

    /**
     * Get families
     *
     * @return ArrayCollection|null
     */
    public function getFamilies();

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get options
     *
     * @return \ArrayAccess
     */
    public function getOptions();

    /**
     * Predicate for negativeAllowed property
     *
     * @return bool $negativeAllowed
     */
    public function isNegativeAllowed();

    /**
     * Returns the minimum input length for singlechoice and multichoice types
     *
     * @return int
     */
    public function getMinimumInputLength();

    /**
     * Set a property
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return AttributeInterface
     */
    public function setProperty($property, $value);

    /**
     * Set group
     *
     * @param AttributeGroupInterface $group
     *
     * @return AttributeInterface
     */
    public function setGroup(AttributeGroupInterface $group = null);

    /**
     * Get allowedExtensions
     *
     * @return array $allowedExtensions
     */
    public function getAllowedExtensions();

    /**
     * Set allowedExtensions
     *
     * @param string $allowedExtensions
     *
     * @return AttributeInterface
     */
    public function setAllowedExtensions($allowedExtensions);

    /**
     * Get a property
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty($property);

    /**
     * Set negativeAllowed
     *
     * @param bool $negativeAllowed
     *
     * @return AttributeInterface
     */
    public function setNegativeAllowed($negativeAllowed);

    /**
     * Is localizable
     *
     * @return bool $localizable
     */
    public function isLocalizable();

    /**
     * Add available locale
     *
     * @param LocaleInterface $availableLocale
     *
     * @return AttributeInterface
     */
    public function addAvailableLocale(LocaleInterface $availableLocale);

    /**
     * Get code
     *
     * @return string
     */
    public function getCode();

    /**
     * Predicate for useableAsGridFilter property
     *
     * @return bool $useableAsGridFilter
     */
    public function isUseableAsGridFilter();

    /**
     * Set families
     *
     * @param ArrayCollection $families
     *
     * @return AttributeInterface
     */
    public function setFamilies(ArrayCollection $families);

    /**
     * Set Validation regexp
     *
     * @param string $validationRegexp
     *
     * @return AttributeInterface
     */
    public function setValidationRegexp($validationRegexp);

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
     *
     * @return AttributeInterface
     */
    public function addFamily(FamilyInterface $family);

    /**
     * Set Validation rule
     *
     * @param string $validationRule
     *
     * @return AttributeInterface
     */
    public function setValidationRule($validationRule);

    /**
     * Set dateMin
     *
     * @param \DateTime $dateMin
     *
     * @return AttributeInterface
     */
    public function setDateMin($dateMin);

    /**
     * Set attribute type
     *
     * @param string $type
     *
     * @return AttributeInterface
     *
     * @deprecated Will be removed in 1.8. Please use setType() instead.
     */
    public function setAttributeType($type);

    /**
     * Set attribute type
     *
     * @param string $type
     *
     * @see Pim\Component\Catalog\AttributeTypes
     *
     * @return AttributeInterface
     */
    public function setType($type);

    /**
     * Set dateMax
     *
     * @param \DateTime $dateMax
     *
     * @return AttributeInterface
     */
    public function setDateMax($dateMax);

    /**
     * Set maxFileSize
     *
     * @param float $maxFileSize
     *
     * @return AttributeInterface
     */
    public function setMaxFileSize($maxFileSize);

    /**
     * Get group
     *
     * @return AttributeGroupInterface
     */
    public function getGroup();

    /**
     * Set label
     *
     * @param string $label
     *
     * @return string
     */
    public function setLabel($label);

    /**
     * Get attribute type
     *
     * @return string
     *
     * @deprecated Will be removed in 1.8. Please use getType() instead.
     */
    public function getAttributeType();

    /**
     * Get attribute type
     *
     * @see Pim\Component\Catalog\AttributeTypes
     *
     * @return string
     */
    public function getType();

    /**
     * Predicate for wysiwygEnabled property
     *
     * @return bool $wysiwygEnabled
     */
    public function isWysiwygEnabled();

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
    public function getValidationRule();

    /**
     * Set properties
     *
     * @param array $properties
     *
     * @return AttributeInterface
     */
    public function setProperties(array $properties);

    /**
     * Is required
     *
     * @return bool $required
     */
    public function isRequired();

    /**
     * Set all parameters with associative array
     *
     * @param array $parameters
     *
     * @throws \Exception
     *
     * @return AttributeInterface
     */
    public function setParameters($parameters);

    /**
     * Set Max characters
     *
     * @param int $maxCharacters
     *
     * @return AttributeInterface
     */
    public function setMaxCharacters($maxCharacters);

    /**
     * Set unique
     *
     * @param bool $unique
     *
     * @return AttributeInterface
     */
    public function setUnique($unique);

    /**
     * Get dateMin
     *
     * @return \DateTime $dateMin
     */
    public function getDateMin();

    /**
     * Remove family
     *
     * @param FamilyInterface $family
     *
     * @return AttributeInterface
     */
    public function removeFamily(FamilyInterface $family);

    /**
     * Set numberMax
     *
     * @param mixed $numberMax
     *
     * @return AttributeInterface
     */
    public function setNumberMax($numberMax);

    /**
     * Set useableAsGridFilter
     *
     * @param bool $useableAsGridFilter
     *
     * @return AttributeInterface
     */
    public function setUseableAsGridFilter($useableAsGridFilter);

    /**
     * Get dateMax
     *
     * @return \DateTime $dateMax
     */
    public function getDateMax();

    /**
     * Sets the minimum input length for singlechoice and multichoice types
     *
     * @param int $minimumInputLength
     *
     * @return AttributeInterface
     */
    public function setMinimumInputLength($minimumInputLength);

    /**
     * Set sortOrder
     *
     * @param number $sortOrder
     *
     * @return AttributeInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Predicate for decimalsAllowed property
     *
     * @return bool $decimalsAllowed
     */
    public function isDecimalsAllowed();

    /**
     * Set defaultMetricUnit
     *
     * @param string $defaultMetricUnit
     *
     * @return AttributeInterface
     */
    public function setDefaultMetricUnit($defaultMetricUnit);

    /**
     * Remove option
     *
     * @param AttributeOptionInterface $option
     *
     * @return AttributeInterface
     */
    public function removeOption(AttributeOptionInterface $option);

    /**
     * Get Max characters
     *
     * @return int $maxCharacters
     */
    public function getMaxCharacters();

    /**
     * Remove available locale
     *
     * @param LocaleInterface $availableLocale
     *
     * @return AttributeInterface
     */
    public function removeAvailableLocale(LocaleInterface $availableLocale);

    /**
     * Set scopable
     *
     * @param bool $scopable
     *
     * @return AttributeInterface
     */
    public function setScopable($scopable);

    /**
     * Get sortOrder
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get Validation regexp
     *
     * @return string $validationRegexp
     */
    public function getValidationRegexp();

    /**
     * Set decimalsAllowed
     *
     * @param bool $decimalsAllowed
     *
     * @return AttributeInterface
     */
    public function setDecimalsAllowed($decimalsAllowed);

    /**
     * Set metricFamily
     *
     * @param string $metricFamily
     *
     * @return AttributeInterface
     */
    public function setMetricFamily($metricFamily);

    /**
     * Check if attribute is locale specific
     *
     * @return bool
     */
    public function isLocaleSpecific();

    /**
     * @return string
     */
    public function getReferenceDataName();

    /**
     * @param string $name
     */
    public function setReferenceDataName($name);

    /**
     * @return bool
     */
    public function isBackendTypeReferenceData();
}
