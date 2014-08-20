<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;

/**
 * Abstract product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProduct implements ProductInterface, LocalizableInterface, ScopableInterface,
 TimestampableInterface, VersionableInterface
{
    /** @staticvar string */
    const IDENTIFIER_TYPE = 'pim_catalog_identifier';

    /** @var mixed $id */
    protected $id;

    /** @var datetime $created */
    protected $created;

    /** @var datetime $updated */
    protected $updated;

    /**
     * Not persisted but allow to force locale for values
     * @var string $locale
     */
    protected $locale;

    /**
     * Not persisted but allow to force scope for values
     * @var string $scope
     */
    protected $scope;

    /** @var ArrayCollection */
    protected $values;

    /** @var array */
    protected $indexedValues;

    /** @var boolean */
    protected $indexedValuesOutdated = true;

    /**
     * @var Pim\Bundle\CatalogBundle\Entity\Family $family
     */
    protected $family;

    /**
     * @var integer
     */
    protected $familyId;

    /**
     * @var ArrayCollection $categories
     */
    protected $categories;

    public $categoryIds = [];

    public $treeIds = [];

    /**
     * @var boolean $enabled
     */
    protected $enabled = true;

    /**
     * @var ArrayCollection $groups
     */
    protected $groups;

    protected $groupIds = [];

    /**
     * @var ArrayCollection $associations
     */
    protected $associations;

    /**
     * @var ArrayCollection $completenesses
     */
    protected $completenesses;

    protected $normalizedData;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->categories     = new ArrayCollection();
        $this->completenesses = new ArrayCollection();
        $this->groups         = new ArrayCollection();
        $this->associations   = new ArrayCollection();
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
     * @return AbstractProduct
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Get used locale
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set used locale
     *
     * @param string $locale
     *
     * @return LocalizableInterface
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get used scope
     * @return string $scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set used scope
     *
     * @param string $scope
     *
     * @return ScopableInterface
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Add value, override to deal with relation owner side
     *
     * @param ProductValueInterface $value
     *
     * @return AbstractProduct
     */
    public function addValue(ProductValueInterface $value)
    {
        $this->values[] = $value;
        $this->indexedValues[$value->getAttribute()->getCode()][] = $value;
        $value->setEntity($this);

        return $this;
    }

    /**
     * Remove value
     *
     * @param ProductValueInterface $value
     *
     * @return AbstractProduct
     */
    public function removeValue(ProductValueInterface $value)
    {
        $this->removeIndexedValue($value);
        $this->values->removeElement($value);

        return $this;
    }

    /**
     * Remove a value from the indexedValues array
     *
     * @param ProductValueInterface $value
     *
     * @return AbstractProduct
     */
    protected function removeIndexedValue(ProductValueInterface $value)
    {
        $attributeCode = $value->getAttribute()->getCode();
        $possibleValues =& $this->indexedValues[$attributeCode];

        if (is_array($possibleValues)) {
            foreach ($possibleValues as $key => $possibleValue) {
                if ($value === $possibleValue) {
                    unset($possibleValues[$key]);
                    break;
                }
            }
        } else {
            unset($this->indexedValues[$attributeCode]);
        }

        return $this;
    }

    /**
     * Get the list of used attribute code from the indexed values
     *
     * @return array
     */
    public function getUsedAttributeCodes()
    {
        return array_keys($this->getIndexedValues());
    }

    /**
     * Build the values indexed by attribute code array
     *
     * @return array indexedValues
     */
    protected function getIndexedValues()
    {
        $this->indexValuesIfNeeded();

        return $this->indexedValues;
    }

    /**
     * Mark the indexed as outdated
     *
     * @return AbstractProduct
     */
    public function markIndexedValuesOutdated()
    {
        $this->indexedValuesOutdated = true;

        return $this;
    }

    /**
     * Build the indexed values if needed. First step
     * is to make sure that the values are initialized
     * (loaded from DB)
     *
     * @return AbstractProduct
     */
    protected function indexValuesIfNeeded()
    {
        if ($this->indexedValuesOutdated) {
            $this->indexedValues = array();
            foreach ($this->values as $value) {
                $this->indexedValues[$value->getAttribute()->getCode()][] = $value;
            }
            $this->indexedValuesOutdated = false;
        }

        return $this;
    }

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     *
     * @return ProductValueInterface
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null)
    {
        $indexedValues = $this->getIndexedValues();

        if (!isset($indexedValues[$attributeCode])) {
            return null;
        }

        $value = null;
        $possibleValues = $indexedValues[$attributeCode];

        if (is_array($possibleValues)) {
            foreach ($possibleValues as $possibleValue) {
                $valueLocale = null;
                $valueScope = null;

                if (null !== $possibleValue->getLocale()) {
                    $valueLocale = ($localeCode) ? $localeCode : $this->getLocale();
                }
                if (null !== $possibleValue->getScope()) {
                    $valueScope = ($scopeCode) ? $scopeCode : $this->getScope();
                }
                if ($possibleValue->getLocale() === $valueLocale && $possibleValue->getScope() === $valueScope) {
                    $value = $possibleValue;
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * Get whether or not an attribute is part of a product
     *
     * @param AbstractEntityAttribute $attribute
     *
     * @return boolean
     */
    public function hasAttribute(AbstractAttribute $attribute)
    {
        $indexedValues = $this->getIndexedValues();

        return isset($indexedValues[$attribute->getCode()]);
    }

    /**
     * Check if a field or attribute exists
     *
     * @param string $attributeCode
     *
     * @return boolean
     */
    public function __isset($attributeCode)
    {
        $indexedValues = $this->getIndexedValues();

        return isset($indexedValues[$attributeCode]);
    }

    /**
     * Get value data by attribute code
     *
     * @param string $attCode
     *
     * @return mixed
     */
    public function __get($attCode)
    {
        return $this->getValue($attCode);
    }

    /**
     * Get family
     *
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Set family
     *
     * @param Family $family
     *
     * @return AbstractProduct
     */
    public function setFamily(Family $family = null)
    {
        if (null !== $family) {
            $this->familyId = $family->getId();
        }
        $this->family = $family;

        return $this;
    }

    /**
     * Set family id
     *
     * @param integer $familyId
     *
     * @return AbstractProduct
     */
    public function setFamilyId($familyId)
    {
        $this->familyId = $familyId;

        return $this;
    }

    /**
     * Get family id
     *
     * @return int
     */
    public function getFamilyId()
    {
        return $this->familyId;

    }

    /**
     * Get the identifier of the product
     *
     * @return ProductValueInterface the identifier of the product
     *
     * @throws MissingIdentifierException if no identifier could be found
     */
    public function getIdentifier()
    {
        foreach ($this->values as $value) {
            if (self::IDENTIFIER_TYPE === $value->getAttribute()->getAttributeType()) {
                return $value;
            }
        }

        throw new MissingIdentifierException($this);
    }

    /**
     * Get the attributes of the product
     *
     * @return array the attributes of the current product
     */
    public function getAttributes()
    {
        $attributes = array();

        foreach ($this->values as $value) {
            $attributes[] = $value->getAttribute();
        }

        return $attributes;
    }

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues()
    {
        $_values = new ArrayCollection();

        foreach ($this->values as $value) {
            $_values[ProductValueKeyGenerator::getKey($value)] = $value;
        }

        return $_values;
    }

    /**
     * Get ordered group
     *
     * @return array
     */
    public function getOrderedGroups()
    {
        $groups = array();

        foreach ($this->getAttributes() as $attribute) {
            $group = $attribute->getGroup();
            $groups[$group->getId()] = $group;
        }

        $sortGroup = function (AttributeGroup $fst, AttributeGroup $snd) {
            return $fst->getSortOrder() - $snd->getSortOrder();
        };

        @usort($groups, $sortGroup);

        return $groups;
    }

    /**
     * Get product label
     *
     * @param string $locale
     *
     * @return mixed|string
     */
    public function getLabel($locale = null)
    {
        if ($this->family) {
            if ($attributeAsLabel = $this->family->getAttributeAsLabel()) {
                if ($locale) {
                    $this->setLocale($locale);
                }
                if ($value = $this->getValue($attributeAsLabel->getCode())) {
                    $data = $value->getData();
                    if (!empty($data)) {
                        return (string) $data;
                    }
                }
            }
        }

        return (string) $this->getIdentifier()->getData();
    }

    /**
     * Get the product categories
     *
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add a category
     * @param CategoryInterface $category
     *
     * @return AbstractProduct
     */
    public function addCategory(CategoryInterface $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addProduct($this);
            $this->treeIds[] = $category->getRoot();
        }

        return $this;
    }

    /**
     * Remove a category
     * @param CategoryInterface $category
     *
     * @return AbstractProduct
     */
    public function removeCategory(CategoryInterface $category)
    {
        $this->categories->removeElement($category);
        unset($this->treeIds[array_search($category->getRoot(), $this->treeIds)]);

        return $this;
    }

    /**
     * Get a string with categories linked to product
     *
     * @return string
     */
    public function getCategoryCodes()
    {
        $codes = array();
        foreach ($this->getCategories() as $category) {
            $codes[] = $category->getCode();
        }

        return implode(',', $codes);
    }

    /**
     * Get a string with groups
     *
     * @return string
     */
    public function getGroupCodes()
    {
        $codes = array();
        foreach ($this->getGroups() as $group) {
            $codes[] = $group->getCode();
        }
        sort($codes);

        return implode(',', $codes);
    }

    /**
     * Predicate to know if product is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Setter for predicate enabled
     *
     * @param boolean $enabled
     *
     * @return AbstractProduct
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Check if an attribute can be removed from the product
     *
     * @param AbstractAttribute $attribute
     *
     * @return boolean
     */
    public function isAttributeRemovable(AbstractAttribute $attribute)
    {
        if ('pim_catalog_identifier' === $attribute->getAttributeType()) {
            return false;
        }

        if (null !== $this->family && $this->family->getAttributes()->contains($attribute)) {
            return false;
        }

        foreach ($this->groups as $group) {
            if ($group->getType()->isVariant()) {
                if ($group->getAttributes()->contains($attribute)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the product groups
     *
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add a group
     * @param Group $group
     *
     * @return Group
     */
    public function addGroup(Group $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addProduct($this);
        }

        return $this;
    }

    /**
     * Remove a group
     * @param Group $group
     *
     * @return AbstractProduct
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getLabel();
    }

    /**
     * Get all the media of the product
     *
     * @return Media[]
     */
    public function getMedia()
    {
        $media = array();
        foreach ($this->getValues() as $value) {
            if (in_array(
                $value->getAttribute()->getAttributeType(),
                array('pim_catalog_image', 'pim_catalog_file')
            )) {
                $media[] = $value->getData();
            }
        }

        return $media;
    }

    /**
     * Add product association
     *
     * @param AbstractAssociation $association
     *
     * @return AbstractProduct
     */
    public function addAssociation(AbstractAssociation $association)
    {
        if (!$this->associations->contains($association)) {
            $association->setOwner($this);
            $this->associations->add($association);
        }

        return $this;
    }

    /**
     * Remove product association
     *
     * @param AbstractAssociation $association
     *
     * @return AbstractProduct
     */
    public function removeAssociation(AbstractAssociation $association)
    {
        $this->associations->removeElement($association);

        return $this;
    }

    /**
     * Get the product associations
     *
     * @return AbstractAssociation[]|null
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Get the product association for an Association type
     *
     * @param AssociationType $type
     *
     * @return AbstractAssociation|null
     */
    public function getAssociationForType(AssociationType $type)
    {
        return $this->getAssociationForTypeCode($type->getCode());
    }

    /**
     * Get the product association for an association type code
     *
     * @param string $typeCode
     *
     * @return AbstractAssociation|null
     */
    public function getAssociationForTypeCode($typeCode)
    {
        foreach ($this->associations as $association) {
            if ($association->getAssociationType()->getCode() === $typeCode) {
                return $association;
            }
        }

        return null;
    }

    /**
     * Set product associations
     *
     * @param AbstractAssociation[] $associations
     *
     * @return AbstractProduct
     */
    public function setAssociations(array $associations = array())
    {
        $this->associations = new ArrayCollection($associations);

        return $this;
    }

    /**
     * Set product completenesses
     *
     * @param ArrayCollection $completenesses
     *
     * @return AbstractProduct
     */
    public function setCompletenesses(ArrayCollection $completenesses)
    {
        $this->completenesses = $completenesses;

        return $this;
    }

    /**
     * Get product completenesses
     *
     * @return ArrayCollection
     */
    public function getCompletenesses()
    {
        return $this->completenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->getIdentifier()->getData();
    }

    /**
     * @param mixed $normalizedData
     */
    public function setNormalizedData($normalizedData)
    {
        $this->normalizedData = $normalizedData;
    }
}
