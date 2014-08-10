<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\Translatable;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;

/**
 * Group entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Assert\GroupSequenceProvider
 *
 * @ExclusionPolicy("all")
 */
class Group implements TranslatableInterface, GroupSequenceProviderInterface, ReferableInterface, VersionableInterface
{
    use Translatable;

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
     * @var GroupType
     */
    protected $type;

    /**
     * @var ArrayCollection $products
     */
    protected $products;

    /**
     * @var ArrayCollection $attributes
     */
    protected $attributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products     = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->attributes   = new ArrayCollection();
    }

    /**
     * Get the id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code
     *
     * @return string $code
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
     * @return Group
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set group type
     *
     * @param GroupType $type
     *
     * @return Group
     */
    public function setType(GroupType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get group type
     *
     * @return Group
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add a product to the collection (if not already existing)
     *
     * @param ProductInterface $product
     *
     * @return Group
     */
    public function addProduct(ProductInterface $product)
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addGroup($this);
        }

        return $this;
    }

    /**
     * Remove a product from the collection
     *
     * @param ProductInterface $product
     *
     * @return Group
     */
    public function removeProduct(ProductInterface $product)
    {
        $this->products->removeElement($product);
        $product->removeGroup($this);

        return $this;
    }

    /**
     * Get products collection
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set a products collection
     *
     * @param array $products
     *
     * @return Group
     */
    public function setProducts(array $products)
    {
        $this->products = new ArrayCollection($products);

        return $this;
    }

    /**
     * Add attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return Group
     */
    public function addAttribute(AbstractAttribute $attribute)
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
        }

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return Group
     *
     * @throws \InvalidArgumentException
     */
    public function removeAttribute(AbstractAttribute $attribute)
    {
        $this->attributes->removeElement($attribute);

        return $this;
    }

    /**
     * Get attributes
     *
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get attribute ids
     *
     * @return integer[]
     */
    public function getAttributeIds()
    {
        return array_map(
            function ($attribute) {
                return $attribute->getId();
            },
            $this->getAttributes()->toArray()
        );
    }

    /**
     * Setter for attributes property
     *
     * @param AbstractAttribute[] $attributes
     *
     * @return Group
     */
    public function setAttributes(array $attributes = array())
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Return the identifier-based validation group for validation of properties
     * @return string[]
     */
    public function getGroupSequence()
    {
        return array('Default', strtolower($this->getType()->getCode()));
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }
}
