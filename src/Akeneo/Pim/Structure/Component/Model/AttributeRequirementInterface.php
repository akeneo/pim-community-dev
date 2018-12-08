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
     *
     * @return AttributeRequirementInterface
     */
    public function setFamily(FamilyInterface $family);

    /**
     * Getter family
     *
     * @return FamilyInterface
     */
    public function getFamily();

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeRequirementInterface
     */
    public function setAttribute(AttributeInterface $attribute);

    /**
     * Get attribute
     *
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Setter channel
     *
     * @param ChannelInterface $channel
     *
     * @return AttributeRequirementInterface
     */
    public function setChannel(ChannelInterface $channel);

    /**
     * Getter channel
     *
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * Get channel code
     *
     * @return string
     */
    public function getChannelCode();

    /**
     * Setter required property
     *
     * @param bool $required
     *
     * @return AttributeRequirementInterface
     */
    public function setRequired($required);

    /**
     * Predicate for required property
     *
     * @return bool
     */
    public function isRequired();
}
