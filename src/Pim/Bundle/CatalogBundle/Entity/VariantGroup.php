<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Variant group entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Variant group", "plural_label"="Variant groups"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class VariantGroup extends Group
{
    /**
     * @var ArrayCollection $attributes
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\CatalogBundle\Entity\ProductAttribute")
     * @ORM\JoinTable(
     *     name="pim_catalog_variant_group_attribute",
     *     joinColumns={@ORM\JoinColumn(name="variant_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")}
     * )
     */
    protected $attributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->attributes   = new ArrayCollection();
    }

    /**
     * Add attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return VariantGroup
     */
    public function addAttribute(ProductAttribute $attribute)
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
        }

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return VariantGroup
     *
     * @throws \InvalidArgumentException
     */
    public function removeAttribute(ProductAttribute $attribute)
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
}
