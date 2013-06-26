<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Oro\Bundle\SegmentationTreeBundle\Entity\AbstractSegment;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

/**
 * Segment class allowing to organize a flexible product class into trees
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\CategoryRepository")
 * @ORM\Table(
 *     name="pim_category",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="pim_category_code_uc", columns={"code"})}
 * )
 * @Gedmo\Tree(type="nested")
 * @Gedmo\TranslationEntity(class="Pim\Bundle\ProductBundle\Entity\CategoryTranslation")
 * @UniqueEntity(fields="code", message="This code is already taken")
 * @Oro\Loggable
 */
class Category extends AbstractSegment implements Translatable
{
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=64)
     * @Oro\Versioned
     */
    protected $code;

    /**
     * @var Category $parent
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned("getCode")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection $children
     *
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent", cascade={"persist"})
     * @ORM\OrderBy({"left" = "ASC"})
     * @Oro\Versioned("getCode")
     */
    protected $children;

    /**
     * @var \Doctrine\Common\Collections\Collection $products
     *
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="categories")
     * @ORM\JoinTable(
     *     name="pim_category_product",
     *     joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $products;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=64)
     * @Gedmo\Translatable
     */
    protected $title;

    /**
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     *
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * Define if a node is dynamic or not
     *
     * @var boolean $isDynamic
     *
     * @ORM\Column(name="is_dynamic", type="boolean")
     */
    protected $isDynamic = false;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="CategoryTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->products     = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return string
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this->title;
    }

    /**
     * Add product to this category node
     *
     * @param Product $product
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Category
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;

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
     * @param Product $product
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Category
     */
    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     * Get products for this category node
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Get products count
     *
     * @return number
     */
    public function getProductsCount()
    {
        return $this->products->count();
    }

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Category
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Predicate to know if node is dynamic
     *
     * @return boolean
     */
    public function getIsDynamic()
    {
        return $this->isDynamic;
    }

    /**
     * Set if a node is dynamic
     *
     * @param boolean $isDynamic
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Category
     */
    public function setIsDynamic($isDynamic)
    {
        $this->isDynamic = $isDynamic;

        return $this;
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
     * Get translations
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Add translation
     *
     * @param CategoryTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Category
     */
    public function addTranslation(CategoryTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * Remove translation
     *
     * @param CategoryTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Category
     */
    public function removeTranslation(CategoryTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
