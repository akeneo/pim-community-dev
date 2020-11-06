<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Channel\Component\Model\ChannelInterface;

/**
 * The attribute requirement for a channel and a family
 *
 * @author    Julien Janvier <jjavnier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRequirementInterface
{
    /**
     * Setter family
     *
     * @param FamilyInterface $family
     */
    public function setFamily(FamilyInterface $family): \Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;

    /**
     * Getter family
     */
    public function getFamily(): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     */
    public function setAttribute(AttributeInterface $attribute): \Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;

    /**
     * Get attribute
     */
    public function getAttribute(): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Get attribute code
     */
    public function getAttributeCode(): string;

    /**
     * Setter channel
     *
     * @param ChannelInterface $channel
     */
    public function setChannel(ChannelInterface $channel): \Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;

    /**
     * Getter channel
     */
    public function getChannel(): \Akeneo\Channel\Component\Model\ChannelInterface;

    /**
     * Get channel code
     */
    public function getChannelCode(): string;

    /**
     * Setter required property
     *
     * @param bool $required
     */
    public function setRequired(bool $required): \Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;

    /**
     * Predicate for required property
     */
    public function isRequired(): bool;
}
