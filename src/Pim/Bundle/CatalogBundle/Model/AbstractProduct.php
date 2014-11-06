<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;

/**
 * Abstract product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProduct implements ProductInterface
{
    /** @staticvar string */
    const IDENTIFIER_TYPE = 'pim_catalog_identifier';

    /** @var int|string */
    protected $id;

    /** @var \Datetime $created */
    protected $created;

    /** @var \Datetime $updated */
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

    /** @var bool */
    protected $indexedValuesOutdated = true;

    /** @var FamilyInterface $family */
    protected $family;

    /** @var int */
    protected $familyId;

    /** @var ArrayCollection $categories */
    protected $categories;

    /** @var array */
    public $categoryIds = [];

    /** @var bool $enabled */
    protected $enabled = true;

    /** @var ArrayCollection $groups */
    protected $groups;

    /** @var array */
    protected $groupIds = [];

    /** @var ArrayCollection $associations */
    protected $associations;

    /** @var ArrayCollection $completenesses */
    protected $completenesses;

    /** @var array */
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
    public function getLocale()
    {
        return $this->locale;
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
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addValue(ProductValueInterface $value)
    {
        $this->values[] = $value;
        $this->indexedValues[$value->getAttribute()->getCode()][] = $value;
        $value->setEntity($this);

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute)
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
     * {@inheritdoc}
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family = null)
    {
        if (null !== $family) {
            $this->familyId = $family->getId();
        }
        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamilyId($familyId)
    {
        $this->familyId = $familyId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyId()
    {
        return $this->familyId;

    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function addCategory(CategoryInterface $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategory(CategoryInterface $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAttributeRemovable(AttributeInterface $attribute)
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
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function addAssociation(AssociationInterface $association)
    {
        if (!$this->associations->contains($association)) {
            $associationType = $association->getAssociationType();
            if (null !== $associationType && null !== $this->getAssociationForType($associationType)) {
                throw new \LogicException(
                    sprintf(
                        'Can not add an association of type %s because the product already has one',
                        $associationType->getCode()
                    )
                );
            }

            $this->associations->add($association);
            $association->setOwner($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAssociation(AssociationInterface $association)
    {
        $this->associations->removeElement($association);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationForType(AssociationType $type)
    {
        return $this->getAssociationForTypeCode($type->getCode());
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setAssociations(array $associations = array())
    {
        $this->associations = new ArrayCollection($associations);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompletenesses(ArrayCollection $completenesses)
    {
        $this->completenesses = $completenesses;

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setNormalizedData($normalizedData)
    {
        $this->normalizedData = $normalizedData;
    }
}
