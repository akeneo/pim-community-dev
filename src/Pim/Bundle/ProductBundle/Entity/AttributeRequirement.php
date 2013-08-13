<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\ConfigBundle\Entity\Channel;

/**
 * The attribute requirement for a channel and a family
 *
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @ORM\Table(name="pim_attribute_requirement")
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
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Family", inversedBy="attributeRequirements")
     */
    protected $family;

    /**
     * @var ProductAttribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\ProductAttribute")
     */
    protected $attribute;

    /**
     * @var Channel $channel
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ConfigBundle\Entity\Channel")
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
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeRequirement
     */
    public function setFamily(Family $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Getter family
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Family
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
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeRequirement
     */
    public function setAttribute(ProductAttribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Getter product attribute
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
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
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeRequirement
     */
    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Getter channel
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
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
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeRequirement
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
