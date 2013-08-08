<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Product family
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product_family")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\FamilyRepository")
 * @UniqueEntity(fields="code", message="This code is already taken.")
 * @Oro\Loggable
 */
class Family implements TranslatableInterface
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
     * @Oro\Versioned
     */
    protected $code;

    /**
     * @var ArrayCollection $attributes
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ProductBundle\Entity\ProductAttribute", cascade={"persist"})
     * @ORM\JoinTable(
     *    name="pim_product_family_attribute",
     *    joinColumns={@ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="CASCADE")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Oro\Versioned("getCode")
     */
    protected $attributes;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale = self::FALLBACK_LOCALE;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\FamilyTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $translations;

    /**
     * @ORM\ManyToOne(targetEntity="ProductAttribute")
     * @ORM\JoinColumn(name="label_attribute_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned("getCode")
     */
    protected $attributeAsLabel;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\AttributeRequirement",
     *     mappedBy="family",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $attributeRequirements;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes            = new ArrayCollection();
        $this->translations          = new ArrayCollection();
        $this->attributeRequirements = new ArrayCollection();
    }

    /**
     * Returns the label of the product family
     *
     * @return string
     */
    public function __toString()
    {
        return ($this->getLabel() != '') ? $this->getLabel() : $this->code;
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
     * @param ProductAttribute $attribute
     *
     * @return Family
     *
     * @throw InvalidArgumentException
     */
    public function addAttribute(ProductAttribute $attribute)
    {
        if ('pim_product_identifier' === $attribute->getAttributeType()) {
            throw new \InvalidArgumentException('Identifier cannot be part of a family.');
        }
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param ProductAttribute $attribute
     */
    public function removeAttribute(ProductAttribute $attribute)
    {
        $this->attributes->removeElement($attribute);
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
     * Check if family has an attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return boolean
     */
    public function hasAttribute(ProductAttribute $attribute)
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * @param ProductAttribute $attributeAsLabel
     */
    public function setAttributeAsLabel($attributeAsLabel)
    {
        $this->attributeAsLabel = $attributeAsLabel;
    }

    /**
     * @return ProductAttribute
     */
    public function getAttributeAsLabel()
    {
        return $this->attributeAsLabel;
    }

    /**
     * @return array
     */
    public function getAttributeAsLabelChoices()
    {
        return $this->attributes->filter(
            function ($attribute) {
                return in_array(
                    $attribute->getAttributeType(),
                    array(
                        'pim_product_text',
                        'pim_product_identifier'
                    )
                );
            }
        )->toArray();
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
        return 'Pim\Bundle\ProductBundle\Entity\FamilyTranslation';
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        $translated = $this->getTranslation()->getLabel();

        return ($translated != '') ? $translated : $this->getTranslation(self::FALLBACK_LOCALE)->getLabel();
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

    public function setAttributeRequirements($attributeRequirements)
    {
        $this->attributeRequirements = $attributeRequirements;

        return $this;
    }

    public function getAttributeRequirements()
    {
        $result = array();

        foreach ($this->attributeRequirements as $requirement) {
            $key = $this->getAttributeRequirementKeyFor(
                $requirement->getAttribute()->getCode(),
                $requirement->getChannel()->getCode()
            );
            $result[$key] = $requirement;
        }

        return $result;
    }

    public function getAttributeRequirementKeyFor($attributeCode, $channelCode)
    {
        return sprintf('%s_%s', $attributeCode, $channelCode);
    }
}
