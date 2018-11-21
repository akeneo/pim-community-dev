<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Channel\Component\Model\ChannelInterface;

/**
 * The attribute requirement for a channel and a family
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequirement implements AttributeRequirementInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Family
     */
    protected $family;

    /**
     * @var AttributeInterface
     */
    protected $attribute;

    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(AttributeInterface $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->attribute->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel(ChannelInterface $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCode()
    {
        return $this->channel->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return $this->required;
    }
}
