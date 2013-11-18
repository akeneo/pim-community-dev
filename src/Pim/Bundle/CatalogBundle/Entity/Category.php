<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Gedmo\Mapping\Annotation as Gedmo;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\SegmentationTreeBundle\Entity\AbstractSegment;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Segment class allowing to organize a flexible product class into trees
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository")
 * @ORM\Table(
 *     name="pim_catalog_category",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="pim_category_code_uc", columns={"code"})}
 * )
 * @Gedmo\Tree(type="nested")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Category", "plural_label"="Categories"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Category extends AbstractSegment implements CategoryInterface, TranslatableInterface
{
    /**
     * @var Category $parent
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\CategoryInterface", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection $children
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Model\CategoryInterface",
     *     mappedBy="parent",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"left" = "ASC"})
     */
    protected $children;

    /**
     * @var \Doctrine\Common\Collections\Collection $products
     *
     * @ORM\ManyToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Model\ProductInterface",
     *     mappedBy="categories",
     *     fetch="EXTRA_LAZY"
     * )
     */
    protected $products;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=100, unique=true)
     */
    protected $code;

    /**
     * Define if a node is dynamic or not
     *
     * @var boolean $isDynamic
     *
     * @ORM\Column(name="is_dynamic", type="boolean")
     */
    protected $dynamic = false;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable
     * @ORM\Column(type="datetime")
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
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Entity\CategoryTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist"}
     * )
     */
    protected $translations;

    /**
     * @var ArrayCollection $channels
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Entity\Channel",
     *     mappedBy="category"
     * )
     */
    protected $channels;

    /**
     * @var integer $version
     *
     * @ORM\Column(name="version", type="integer")
     * @ORM\Version
     */
    protected $version;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->products     = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->channels     = new ArrayCollection();
    }

    /**
     * Add product to this category node
     *
     * @param ProductInterface $product
     *
     * @return Category
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
     * @return Category
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
     * Get products count
     *
     * @return number
     */
    public function getProductsCount()
    {
        return $this->products->count();
    }

    /**
     * Predicate to know if node is dynamic
     *
     * @return boolean
     */
    public function isDynamic()
    {
        return $this->dynamic;
    }

    /**
     * Set if a node is dynamic
     *
     * @param boolean $dynamic
     *
     * @return Category
     */
    public function setDynamic($dynamic)
    {
        $this->dynamic = $dynamic;

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
     * @return string
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
}
