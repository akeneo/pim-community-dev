<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\ProductAssociation;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Config(
 *     defaultValues={
 *         "entity"={"label"="Product", "plural_label"="Products"},
 *         "security"={
 *             "type"="ACL",
 *             "group_name"=""
 *         }
 *     }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Product extends AbstractEntityFlexible implements ProductInterface
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
     * @var ArrayCollection $productAssociations
     */
    protected $productAssociations;

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

        $this->categories          = new ArrayCollection();
        $this->completenesses      = new ArrayCollection();
        $this->groups              = new ArrayCollection();
        $this->productAssociations = new ArrayCollection();
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
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
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
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues()
    {
        $_values = new ArrayCollection();

        foreach ($this->values as $value) {
            $attribute = $value->getAttribute();
            $key = $attribute->getCode();
            if ($attribute->getTranslatable()) {
                $key .= '_'.$value->getLocale();
            }
            if ($attribute->getScopable()) {
                $key .= '_'.$value->getScope();
            }
            $_values[$key] = $value;
        }

        return $_values;
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
     * @param ProductAttribute $attribute
     *
     * @return boolean
     */
    public function isAttributeRemovable(ProductAttribute $attribute)
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
     * Getter for product completenesses
     *
     * @return ArrayCollection
     */
    public function getCompletenesses()
    {
        return $this->completenesses;
    }

    /**
     * Add product completeness
     *
     * @param Completeness $completeness
     *
     * @return Product
     */
    public function addCompleteness(Completeness $completeness)
    {
        if (!$this->completenesses->contains($completeness)) {
            $this->completenesses->add($completeness);
        }

        return $this;
    }

    /**
     * Remove product completeness
     *
     * @param Completeness $completeness
     *
     * @return Product
     */
    public function removeCompleteness(Completeness $completeness)
    {
        $this->completenesses->removeElement($completeness);

        return $this;
    }

    /**
     * Get the product completeness from a locale and a scope
     *
     * @param string $locale
     * @param string $channel
     *
     * @return Completeness|null
     */
    public function getCompleteness($locale, $channel)
    {
        $completeness = array_filter(
            $this->completenesses->toArray(),
            function ($completeness) use ($locale, $channel) {
                return $completeness->getLocale()->getCode() === $locale
                    && $completeness->getChannel()->getCode() === $channel;
            }
        );

        if (count($completeness) === 0) {
            return null;
        } else {
            return array_shift($completeness);
        }
    }

    /**
     * Set product completenesses
     *
     * @param array $completenesses
     *
     * @return Product
     */
    public function setCompletenesses(array $completenesses = array())
    {
        $this->completenesses = new ArrayCollection($completenesses);

        return $this;
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
     * Add product productAssociation
     *
     * @param ProductAssociation $productAssociation
     *
     * @return Product
     */
    public function addProductAssociation(ProductAssociation $productAssociation)
    {
        if (!$this->productAssociations->contains($productAssociation)) {
            $productAssociation->setOwner($this);
            $this->productAssociations->add($productAssociation);
        }

        return $this;
    }

    /**
     * Remove product productAssociation
     *
     * @param ProductAssociation $productAssociation
     *
     * @return Product
     */
    public function removeProductAssociation(ProductAssociation $productAssociation)
    {
        $this->productAssociations->removeElement($productAssociation);

        return $this;
    }

    /**
     * Get the product productAssociations
     *
     * @return ProductAssociation[]|null
     */
    public function getProductAssociations()
    {
        return $this->productAssociations;
    }

    /**
     * Get the product productAssociation for an Association entity
     *
     * @param Association $association
     *
     * @return ProductAssociation|null
     */
    public function getProductAssociationForAssociation(Association $association)
    {
        return $this->productAssociations->filter(
            function ($productAssociation) use ($association) {
                return $productAssociation->getAssociation() === $association;
            }
        )->first();
    }

    /**
     * Set product productAssociations
     *
     * @param ProductAssociation[] $productAssociations
     *
     * @return Product
     */
    public function setProductAssociations(array $productAssociations = array())
    {
        $this->productAssociations = new ArrayCollection($productAssociations);

        return $this;
    }
}
