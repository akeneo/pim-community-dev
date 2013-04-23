<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Gedmo\Translatable\Translatable;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\SegmentationTreeBundle\Entity\AbstractSegment;

/**
 * Segment class allowing to organize a flexible product class into trees
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository")
 * @ORM\Table(
 *     name="pim_product_segment",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="pim_product_segment_code_uc", columns={"code"})}
 * )
 * @Gedmo\Tree(type="nested")
 * @Gedmo\TranslationEntity(class="Pim\Bundle\ProductBundle\Entity\ProductSegmentTranslation")
 * @UniqueEntity("code")
 */
class ProductSegment extends AbstractSegment implements Translatable
{
    /**
     * @var ProductSegment $parent
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ProductSegment", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection $children
     *
     * @ORM\OneToMany(targetEntity="ProductSegment", mappedBy="parent", cascade={"persist"})
     * @ORM\OrderBy({"left" = "ASC"})
     */
    protected $children;

    /**
     * @var \Doctrine\Common\Collections\Collection $products
     *
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(
     *     name="pim_segments_products",
     *     joinColumns={@ORM\JoinColumn(name="segment_id", referencedColumnName="id")},
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
     * Segment code
     *
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=64)
     */
    protected $code;

    /**
     * Define if a node is dynamic or not
     *
     * @var boolean $isDynamic
     *
     * @ORM\Column(name="is_dynamic", type="boolean")
     */
    protected $isDynamic = false;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="ProductSegmentTranslation",
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
     * Add product to this segment node
     *
     * @param Product $product
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegment
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product for this segment node
     *
     * @param Product $product
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegment
     */
    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     * Get products for this segment node
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return ProductSegment
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

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
     * Set code
     *
     * @param string $code
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegment
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegment
     */
    public function setIsDynamic($isDynamic)
    {
        $this->isDynamic = $isDynamic;

        return $this;
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
     * @param ProductSegmentTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegment
     */
    public function addTranslation(ProductSegmentTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * Remove translation
     *
     * @param ProductSegmentTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegment
     */
    public function removeTranslation(ProductSegmentTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
