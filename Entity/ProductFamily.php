<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Gedmo\Translatable\Translatable;
use Pim\Bundle\ProductBundle\Entity\ProductFamilyTranslation;

/**
 * Product family
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product_family")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\ProductFamilyRepository")
 * @UniqueEntity(fields="code", message="This code is already taken.")
 * @Gedmo\TranslationEntity(class="Pim\Bundle\ProductBundle\Entity\ProductFamilyTranslation")
 */
class ProductFamily implements Translatable
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $code
     *
     * @ORM\Column(unique=true)
     * @Assert\Regex(pattern="/^[a-zA-Z0-9]+$/", message="The code must only contain alphanumeric characters.")
     */
    protected $code;

    /**
     * @var string $label
     *
     * @ORM\Column(nullable=true)
     * @Gedmo\Translatable
     */
    protected $label;

    /**
     * @var ArrayCollection $attributes
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ProductBundle\Entity\ProductAttribute")
     * @ORM\JoinTable(
     *    name="pim_product_family_attribute",
     *    joinColumns={@ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="CASCADE")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
    */
    protected $attributes;

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
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="ProductFamilyTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes   = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * Returns the code of the product family
     *
     * @return string
     */
    public function __toString()
    {
        return $this->code;
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
     * @return ProductAttribute
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Add attribute
     *
     * @param \Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute
     *
     * @return ProductFamily
     */
    public function addAttribute(\Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute)
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * Remove attributes
     *
     * @param \Pim\Bundle\ProductBundle\Entity\ProductAttribute $attributes
     */
    public function removeAttribute(\Pim\Bundle\ProductBundle\Entity\ProductAttribute $attributes)
    {
        $this->attributes->removeElement($attributes);
    }

    /**
     * Get attributes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Check if family has an attribute
     *
     * @param \Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute
     *
     * @return boolean
     */
    public function hasAttribute(\Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute)
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return ProductAttribute
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label
     *
     * return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return Pim\Bundle\ProductBundle\Entity\ProductFamily
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

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
     * @param AttributeGroupTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function addTranslation(ProductFamilyTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * Remove translation
     *
     * @param AttributeGroupTranslation $translation
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function removeTranslation(ProductFamilyTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
