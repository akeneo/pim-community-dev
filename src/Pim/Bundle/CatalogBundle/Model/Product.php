<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class Product extends AbstractEntityFlexible implements ProductInterface, ReferableInterface
{
    /**
     * @var ArrayCollection $values
     */
    protected $values;

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

    /**
     * @var boolean $enabled
     */
    protected $enabled = true;

    /**
     * @var ArrayCollection $groups
     */
    protected $groups;

    /**
     * @var ArrayCollection $associations
     */
    protected $associations;

    /**
     * @var ArrayCollection $completenesses
     */
    protected $completenesses;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->categories     = new ArrayCollection();
        $this->completenesses = new ArrayCollection();
        $this->groups         = new ArrayCollection();
        $this->associations   = new ArrayCollection();
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
     * @return Product
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
     * @return Product
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
        $values = array_filter(
            $this->getValues()->toArray(),
            function ($value) {
                return $value->getAttribute()->getAttributeType() === 'pim_catalog_identifier';
            }
        );

        if (false === $identifier = reset($values)) {
            throw new MissingIdentifierException($this);
        }

        return $identifier;
    }

    /**
     * Get the attributes of the product
     *
     * @return array the attributes of the current product
     */
    public function getAttributes()
    {
        return array_map(
            function ($value) {
                return $value->getAttribute();
            },
            $this->getValues()->toArray()
        );
    }

    /**
     * Get ordered group
     *
     * Group with negative sort order (Other) will be put at the end
     *
     * @return array
     */
    public function getOrderedGroups()
    {
        $firstGroups = array();
        $lastGroups = array();

        foreach ($this->getAttributes() as $attribute) {
            $group = $attribute->getVirtualGroup();
            if ($group->getSortOrder() < 0) {
                $lastGroups[$group->getId()] = $group;
            } else {
                $firstGroups[$group->getId()] = $group;
            }
        }

        $sortGroup = function (AttributeGroup $fst, AttributeGroup $snd) {
            return $fst->getSortOrder() - $snd->getSortOrder();
        };

        @usort($firstGroups, $sortGroup);
        @usort($lastGroups, $sortGroup);

        return array_merge($firstGroups, $lastGroups);
    }

    /**
     * Get product label
     *
     * @param string $locale
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\mixed|string
     */
    public function getLabel($locale = null)
    {
        if ($this->family) {
            if ($attributeAsLabel = $this->family->getAttributeAsLabel()) {
                if ($locale) {
                    $this->setLocale($locale);
                }
                if ($value = $this->getValue($attributeAsLabel)) {
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
     * @param Category $category
     *
     * @return Product
     */
    public function addCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * Remove a category
     * @param Category $category
     *
     * @return Product
     */
    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);

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
     * @return Product
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Check if an attribute can be removed from the product
     *
     * @param AttributeInterface $attribute
     *
     * @return boolean
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
        }

        return $this;
    }

    /**
     * Remove a group
     * @param Group $group
     *
     * @return Product
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
     * @param Association $association
     *
     * @return Product
     */
    public function addAssociation(Association $association)
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
     * @param Association $association
     *
     * @return Product
     */
    public function removeAssociation(Association $association)
    {
        $this->associations->removeElement($association);

        return $this;
    }

    /**
     * Get the product associations
     *
     * @return Association[]|null
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
     * @return Association|null
     */
    public function getAssociationForType(AssociationType $type)
    {
        return $this->associations->filter(
            function ($association) use ($type) {
                return $association->getAssociationType() === $type;
            }
        )->first();
    }

    /**
     * Set product associations
     *
     * @param Association[] $associations
     *
     * @return Product
     */
    public function setAssociations(array $associations = array())
    {
        $this->associations = new ArrayCollection($associations);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->getIdentifier()->getData();
    }
}
