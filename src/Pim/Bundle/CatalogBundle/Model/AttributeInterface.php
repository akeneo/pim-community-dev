<?php
namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
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
     * @param boolean $required
     *
     * @return AttributeInterface
     */
    public function setRequired($required);

    /**
     * Is unique
     * @return boolean $unique
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
     * @return array
     */
    public function getProperties();

    /**
     * Get backend type
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
     * @return boolean $scopable
     */
    public function isScopable();

    /**
     * Set localizable
     *
     * @param boolean $localizable
     *
     * @return AttributeInterface
     */
    public function setLocalizable($localizable);

    /**
     * Get entity type
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
     * Get available locales
     *
     * @deprecated will be removed in 1.4, use getLocaleSpecificCodes
     *
     * @return ArrayCollection|null
     */
    public function getAvailableLocales();

    /**
     * Get available locale codes
     *
     * @return array
     */
    public function getLocaleSpecificCodes();

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
     * @return double $maxFileSize
     */
    public function getMaxFileSize();

    /**
     * Set wysiwygEnabled
     *
     * @param boolean $wysiwygEnabled
     *
     * @return AttributeInterface
     */
    public function setWysiwygEnabled($wysiwygEnabled);

    /**
     * Get metricFamily
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
     * @return string $defaultMetricUnit
     */
    public function getDefaultMetricUnit();

    /**
     * Get numberMax
     * @return mixed $numberMax
     */
    public function getNumberMax();

    /**
     * Get families
     * @return ArrayCollection|null
     */
    public function getFamilies();

    /**
     * Get label
     * @return string
     */
    public function getLabel();

    /**
     * Get options
     * @return \ArrayAccess
     */
    public function getOptions();

    /**
     * Predicate for negativeAllowed property
     * @return boolean $negativeAllowed
     */
    public function isNegativeAllowed();

    /**
     * Returns the minimum input length for singlechoice and multichoice types
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
     * @param boolean $negativeAllowed
     *
     * @return AttributeInterface
     */
    public function setNegativeAllowed($negativeAllowed);

    /**
     * Is localizable
     * @return boolean $localizable
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
     * @return boolean $useableAsGridFilter
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
     * @param \Datetime $dateMin
     *
     * @return AttributeInterface
     */
    public function setDateMin($dateMin);

    /**
     * {@inheritdoc}
     */
    public function setAttributeType($type);

    /**
     * Set dateMax
     *
     * @param \Datetime $dateMax
     *
     * @return AttributeInterface
     */
    public function setDateMax($dateMax);

    /**
     * Set maxFileSize
     *
     * @param double $maxFileSize
     *
     * @return AttributeInterface
     */
    public function setMaxFileSize($maxFileSize);

    /**
     * Get group
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
     * Get frontend type
     *
     * @return string
     */
    public function getAttributeType();

    /**
     * Predicate for wysiwygEnabled property
     *
     * @return boolean $wysiwygEnabled
     */
    public function isWysiwygEnabled();

    /**
     * Get id
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
     * @return boolean $required
     */
    public function isRequired();

    /**
     * Set all parameters with associative array
     *
     * @param array $parameters
     *
     * @return AttributeInterface
     * @throws \Exception
     */
    public function setParameters($parameters);

    /**
     * Set Max characters
     *
     * @param integer $maxCharacters
     *
     * @return AttributeInterface
     */
    public function setMaxCharacters($maxCharacters);

    /**
     * Set unique
     *
     * @param boolean $unique
     *
     * @return AttributeInterface
     */
    public function setUnique($unique);

    /**
     * Get dateMin
     * @return \Datetime $dateMin
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
     * @param boolean $useableAsGridFilter
     *
     * @return AttributeInterface
     */
    public function setUseableAsGridFilter($useableAsGridFilter);

    /**
     * Get dateMax
     *
     * @return \Datetime $dateMax
     */
    public function getDateMax();

    /**
     * Sets the minimum input length for singlechoice and multichoice types
     *
     * @param integer $minimumInputLength
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
     * @return boolean $decimalsAllowed
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
     * @return integer $maxCharacters
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
     * @param boolean $scopable
     *
     * @return AttributeInterface
     */
    public function setScopable($scopable);

    /**
     * Get sortOrder
     * @return integer
     */
    public function getSortOrder();

    /**
     * Get Validation regexp
     * @return string $validationRegexp
     */
    public function getValidationRegexp();

    /**
     * Set available locales
     *
     * @param ArrayCollection $availableLocales
     *
     * @return AttributeInterface
     */
    public function setAvailableLocales(ArrayCollection $availableLocales);

    /**
     * Set decimalsAllowed
     *
     * @param boolean $decimalsAllowed
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
}
