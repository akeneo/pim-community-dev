<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * The attribute requirement for a channel and a family
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class AttributeRequirement
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var Family $family
     */
    protected $family;

    /**
     * @var AttributeInterface $attribute
     */
    protected $attribute;

    /**
     * @var Channel $channel
     */
    protected $channel;

    /**
     * @var boolean $required
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
     * Set attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeRequirement
     */
    public function setAttribute(AttributeInterface $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return AttributeInterface
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
