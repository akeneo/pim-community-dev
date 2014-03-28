<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

/**
 * Segment class allowing to organize a flexible product class into trees
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Config(
 *     defaultValues={
 *         "entity"={"label"="Category", "plural_label"="Categories"}
 *     }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Category implements CategoryInterface, TranslatableInterface, ReferableInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
     * @var integer $left
     */
    protected $left;

    /**
     * @var integer $level
     */
    protected $level;

    /**
     * @var integer $right
     */
    protected $right;

    /**
     * @var integer $root
     */
    protected $root;

    /**
     * @var CategoryInterface $parent
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection $children
     */
    protected $children;

    /**
     * @var \Doctrine\Common\Collections\Collection $products
     */
    protected $products;

    /**
     * @var datetime
     */
    protected $created;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale;

    /**
     * @var ArrayCollection $translations
     */
    protected $translations;

    /**
     * @var ArrayCollection $channels
     */
    protected $channels;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products     = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->channels     = new ArrayCollection();
        $this->children     = new ArrayCollection();
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
     * Set code
     *
     * @param string $code
     *
     * @return CategoryInterface
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set left
     * @param integer $left
     *
     * @return CategoryInterface
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * Get left
     *
     * @return integer
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set right
     * @param integer $right
     *
     * @return CategoryInterface
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * Get right
     *
     * @return integer
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(CategoryInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(CategoryInterface $child)
    {
        $child->setParent($this);
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove children
     *
     * @param CategoryInterface $children
     */
    public function removeChild(CategoryInterface $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Has children
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * If a node is a tree root, it's the tree starting point and therefore
     * defines the tree itself.
     *
     * @return boolean
     */
    public function isRoot()
    {
        return ($this->getParent() === null);
    }

    /**
     * Add product to this category node
     *
     * @param ProductInterface $product
     *
     * @return CategoryInterface
     */
    public function addProduct(ProductInterface $product)
    {
        $this->products[] = $product;
        $product->addCategory($this);

        return $this;
    }

    /**
     * Predicate to know if a category has product(s) linked
     *
     * @return boolean
     */
    public function hasProducts()
    {
        return $this->products->count() !== 0;
    }

    /**
     * Remove product for this category node
     *
     * @param ProductInterface $product
     *
     * @return CategoryInterface
     */
    public function removeProduct(ProductInterface $product)
    {
        $this->products->removeElement($product);
        $product->removeCategory($this);

        return $this;
    }

    /**
     * Get products for this category node
     *
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set products for this category node
     *
     * @param mixed $products Traversable object or array
     *
     * @return CategoryInterface
     */
    public function setProducts($products)
    {
        if (null !== $this->getProducts()) {
            foreach ($this->getProducts() as $product) {
                $product->removeCategory($this);
            }
        }

        if (null !== $products) {
            foreach ($products as $product) {
                $product->addCategory($this);
            }
        }
    }

    /**
     * Get products count
     *
     * @return integer
     */
    public function getProductsCount()
    {
        return $this->products->count();
    }

    /**
     * Get created date
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
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
        return 'Pim\Bundle\CatalogBundle\Entity\CategoryTranslation';
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
     * @return CategoryInterface
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * Returns the channels linked to the category
     *
     * @return ArrayCollection
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }
}
