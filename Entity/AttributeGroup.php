<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Gedmo\Translatable\Translatable;

use Gedmo\Mapping\Annotation as Gedmo;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute Group entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\AttributeGroupRepository")
 * @ORM\Table(name="pim_attribute_group")
 * @Gedmo\TranslationEntity(class="Pim\Bundle\ProductBundle\Entity\AttributeGroupTranslation")
 */
class AttributeGroup implements TimestampableInterface, Translatable
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100)
     * @Gedmo\Translatable
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

    /**
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var ArrayCollection $attributes
     *
     * @ORM\OneToMany(targetEntity="ProductAttribute", mappedBy="group")
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
     * @var string $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="AttributeGroupTranslation",
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
        $this->attributes = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->name = '';
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
        $this->name = $name;

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
     * @param \Pim\Bundle\ProductBundle\Entity\ProductAttribute $attributes
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function addAttribute(\Pim\Bundle\ProductBundle\Entity\ProductAttribute $attributes)
    {
        $this->attributes[] = $attributes;

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
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get translations
     *
     * @return string
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
    public function addTranslation(AttributeGroupTranslation $translation)
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
    public function removeTranslation(AttributeGroupTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
