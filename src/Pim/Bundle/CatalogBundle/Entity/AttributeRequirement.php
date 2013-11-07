<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * The attribute requirement for a channel and a family
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @ORM\Table(name="pim_catalog_attribute_requirement")
 *
 * @ExclusionPolicy("all")
 */
class AttributeRequirement
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Family $family
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\Family", inversedBy="requirements")
     * @ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $family;

    /**
     * @var ProductAttribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\ProductAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $attribute;

    /**
     * @var Channel $channel
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $channel;

    /**
     * @var boolean $required
     *
     * @ORM\Column(type="boolean")
     */
    protected $required = false;

    /**
     * Setter family
     *
     * @param Family $family
     *
     * @return AttributeRequirement
     */
    public function setFamily(Family $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Getter family
     *
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Setter product attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return AttributeRequirement
     */
    public function setAttribute(ProductAttribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Getter product attribute
     *
     * @return ProductAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Setter channel
     *
     * @param Channel $channel
     *
     * @return AttributeRequirement
     */
    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Getter channel
     *
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Setter required property
     *
     * @param boolean $required
     *
     * @return AttributeRequirement
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Predicate for required property
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }
}
