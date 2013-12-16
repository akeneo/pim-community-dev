<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Group entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Assert\GroupSequenceProvider
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Group", "plural_label"="Groups"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Group implements TranslatableInterface, GroupSequenceProviderInterface
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
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $translations
     */
    protected $translations;

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
    public function getTranslations()
    {
        return $this->translations;
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
        return 'Pim\Bundle\CatalogBundle\Entity\GroupTranslation';
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        $translated = $this->getTranslation() ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Group
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
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
            $this->products[] = $product;
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
     * @param ProductAttributeInterface $attribute
     *
     * @return Group
     */
    public function addAttribute(ProductAttributeInterface $attribute)
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
        }

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param ProductAttributeInterface $attribute
     *
     * @return Group
     *
     * @throws \InvalidArgumentException
     */
    public function removeAttribute(ProductAttributeInterface $attribute)
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
     * @param ProductAttributeInterface[] $attributes
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
     * Returns the label of the group
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }
}
