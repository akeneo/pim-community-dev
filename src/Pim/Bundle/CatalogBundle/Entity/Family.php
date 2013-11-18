<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Gedmo\Mapping\Annotation as Gedmo;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Family entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_catalog_family")
 * @ORM\Entity(repositoryClass="Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Family", "plural_label"="Families"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 *
 * @ExclusionPolicy("all")
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
     * @ORM\Column(name="code", type="string", length=100, unique=true)
     */
    protected $code;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $attributes
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\CatalogBundle\Entity\ProductAttribute", cascade={"persist"})
     * @ORM\JoinTable(
     *    name="pim_catalog_family_attribute",
     *    joinColumns={@ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="CASCADE")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
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
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Entity\FamilyTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist"}
     * )
     */
    protected $translations;

    /**
     * @ORM\ManyToOne(targetEntity="ProductAttribute")
     * @ORM\JoinColumn(name="label_attribute_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $attributeAsLabel;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $requirements
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Entity\AttributeRequirement",
     *     mappedBy="family",
     *     cascade={"persist"}
     * )
     */
    protected $requirements;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Model\ProductInterface",
     *     mappedBy="family"
     * )
     */
    protected $products;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes   = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->requirements = new ArrayCollection();
        $this->products     = new ArrayCollection();
    }

    /**
     * Returns the label of the family
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
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
     * Get created datetime
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param datetime $created
     *
     * @return TimestampableInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param datetime $updated
     *
     * @return TimestampableInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
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
     * @return Family
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
     */
    public function addAttribute(ProductAttribute $attribute)
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return Family
     *
     * @throws InvalidArgumentException
     */
    public function removeAttribute(ProductAttribute $attribute)
    {
        if ('pim_catalog_identifier' === $attribute->getAttributeType()) {
            throw new \InvalidArgumentException('Identifier cannot be removed from a family.');
        }

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
     * Get grouped attributes
     *
     * @return ProductAttribute[]
     */
    public function getGroupedAttributes()
    {
        $result = array();
        foreach ($this->attributes as $attribute) {
            $result[(string) $attribute->getVirtualGroup()][] = $attribute;
        }

        return $result;
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
     *
     * @return Family
     */
    public function setAttributeAsLabel($attributeAsLabel)
    {
        $this->attributeAsLabel = $attributeAsLabel;

        return $this;
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
                        'pim_catalog_text',
                        'pim_catalog_identifier'
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
        return 'Pim\Bundle\CatalogBundle\Entity\FamilyTranslation';
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
     * @return Family
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * Add attribute requirement
     *
     * @param AttributeRequirement $requirement
     *
     * @return Family
     */
    public function addAttributeRequirement(AttributeRequirement $requirement)
    {
        if (!$this->requirements->contains($requirement)) {
            $requirement->setFamily($this);
            $this->requirements->add($requirement);
        }

        return $this;
    }

    /**
     * Set attribute requirements
     *
     * @param array $requirements
     *
     * @return Family
     */
    public function setAttributeRequirements(array $requirements)
    {
        $this->requirements = new ArrayCollection($requirements);

        return $this;
    }

    /**
     * Get attribute requirements
     *
     * @return array
     */
    public function getAttributeRequirements()
    {
        $result = array();

        foreach ($this->requirements as $requirement) {
            $key = $this->getAttributeRequirementKeyFor(
                $requirement->getAttribute()->getCode(),
                $requirement->getChannel()->getCode()
            );
            $result[$key] = $requirement;
        }

        return $result;
    }

    /**
     * Get attribute requirement key
     *
     * @param string $attributeCode
     * @param string $channelCode
     *
     * @return string
     */
    public function getAttributeRequirementKeyFor($attributeCode, $channelCode)
    {
        return sprintf('%s_%s', $attributeCode, $channelCode);
    }

    /**
     * Getter for products
     *
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set products
     *
     * @param array $products
     *
     * @return Product
     */
    public function setProducts(array $products)
    {
        $this->products = new ArrayCollection($products);

        return $this;
    }
}
