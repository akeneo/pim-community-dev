<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_catalog_product")
 * @ORM\Entity(repositoryClass="Pim\Bundle\CatalogBundle\Entity\Repository\ProductRepository")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Product", "plural_label"="Products"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Product extends AbstractEntityFlexible implements ProductInterface, VersionableInterface
{
    /**
     * @var integer $version
     *
     * @ORM\Column(name="version", type="integer")
     * @ORM\Version
     */
    protected $version;

    /**
     * @var ArrayCollection $values
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Model\ProductValueInterface",
     *     mappedBy="entity",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $values;

    /**
     * @var Pim\Bundle\CatalogBundle\Entity\Family $family
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\Family", cascade={"persist"})
     * @ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $family;

    /**
     * @var ArrayCollection $categories
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\CatalogBundle\Model\CategoryInterface", mappedBy="products")
     */
    protected $categories;

    /**
     * @var boolean $enabled
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    protected $enabled = true;

    /**
     * @var VariantGroup $variantGroup
     *
     * @ORM\ManyToOne(targetEntity="VariantGroup", inversedBy="products")
     * @ORM\JoinColumn(name="variant_group_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $variantGroup;

    /**
     * @var ArrayCollection $groups
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\CatalogBundle\Entity\Group", mappedBy="products")
     */
    protected $groups;

    /**
     * @var ArrayCollection $completenesses
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Entity\Completeness",
     *     mappedBy="product",
     *     cascade={"persist", "remove"}
     * )
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
     * @return ProductValue the identifier of the product
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
     * @return \Oro\Bundle\FlexibleEntityBundle\Model\mixed|string
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
            $category->addProduct($this);
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
        $category->removeProduct($this);

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

        if (null === $this->family || !$this->family->getAttributes()->contains($attribute)) {
            if (null === $this->variantGroup || !$this->variantGroup->getAttributes()->contains($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get variant group
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\VariantGroup
     */
    public function getVariantGroup()
    {
        return $this->variantGroup;
    }

    /**
     * Set variant group
     *
     * @param VariantGroup $variantGroup
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     */
    public function setVariantGroup(VariantGroup $variantGroup = null)
    {
        $this->variantGroup = $variantGroup;

        return $this;
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
            $group->addProduct($this);
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
        $group->removeProduct($this);

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
}
