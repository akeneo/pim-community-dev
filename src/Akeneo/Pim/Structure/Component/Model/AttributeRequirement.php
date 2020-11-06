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
    public function setFamily(FamilyInterface $family): AttributeRequirementInterface
    {
        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily(): FamilyInterface
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(AttributeInterface $attribute): AttributeRequirementInterface
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(): \Akeneo\Pim\Structure\Component\Model\AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode(): string
    {
        return $this->attribute->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel(ChannelInterface $channel): AttributeRequirementInterface
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel(): \Akeneo\Channel\Component\Model\ChannelInterface
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCode(): string
    {
        return $this->channel->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setRequired(bool $required): AttributeRequirementInterface
    {
        $this->required = (bool) $required;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired(): bool
    {
        return $this->required;
    }
}
