<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Attribute Group entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\AttributeGroupRepository")
 * @ORM\Table(name="pim_attribute_group")
 */
class AttributeGroup implements TimestampableInterface, TranslatableInterface
{
    /**
     * @staticvar string
     */
    const DEFAULT_GROUP_CODE = 'Other';

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
     * @ORM\Column(name="code", type="string", length=100)
     */
    protected $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

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
     * @var ArrayCollection $attributes
     *
     * @ORM\OneToMany(targetEntity="ProductAttribute", mappedBy="group")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
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
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\AttributeGroupTranslation",
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
        $this->sortOrder = 0;
    }

    /**
     * Returns the name of the attribute group
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
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
     * Set id
     *
     * @param integer $id
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get sort order
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get created
     *
     * @return dateTime
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
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
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
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Add attributes
     *
     * @param \Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function addAttribute(\Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute)
    {
        $this->attributes[] = $attribute;
        $attribute->setGroup($this);

        return $this;
    }

    /**
     * Remove attributes
     *
     * @param \Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function removeAttribute(\Pim\Bundle\ProductBundle\Entity\ProductAttribute $attribute)
    {
        $this->attributes->removeElement($attribute);
        $attribute->setGroup(null);

        return $this;
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
     * Check if the group has an attribute
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
        return 'Pim\Bundle\ProductBundle\Entity\AttributeGroupTranslation';
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        if ($this->getCode() === self::DEFAULT_GROUP_CODE) {
            return self::DEFAULT_GROUP_CODE;
        }

        $translated = $this->getTranslation() ? $this->getTranslation()->getName() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['. $this->getCode() .']';
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function setName($name)
    {
        $this->getTranslation()->setName($name);

        return $this;
    }
}
