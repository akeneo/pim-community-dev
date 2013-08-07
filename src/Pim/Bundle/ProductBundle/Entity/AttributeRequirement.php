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
 */
class AttributeRequirement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Family", inversedBy="AttributeRequirements")
     */
    protected $family;

    /**
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\ProductAttribute")
     */
    protected $attribute;

    /**
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ConfigBundle\Entity\Channel")
     */
    protected $channel;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $required = true;

    public function setFamily(Family $family)
    {
        $this->family = $family;

        return $this;
    }

    public function getFamily()
    {
        return $this->family;
    }

    public function setAttribute(ProductAttribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    public function isRequired()
    {
        return $this->required;
    }
}
