<?php

namespace Akeneo\Pim\Structure\Component\Factory;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;

/**
 * Creates and configures an attribute requirement instance.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequirementFactory
{
    /** @var string */
    protected $attrRequirementClass;

    /**
     * @param string $attrRequirementClass
     */
    public function __construct($attrRequirementClass)
    {
        $this->attrRequirementClass = $attrRequirementClass;
    }

    /**
     * This method creates an attribute requirement instance.
     * Attribute, channel and requirement are set after instantiation.
     *
     * @param AttributeInterface $attribute
     * @param ChannelInterface   $channel
     * @param bool               $required
     *
     * @return AttributeRequirementInterface
     */
    public function createAttributeRequirement(AttributeInterface $attribute, ChannelInterface $channel, $required)
    {
        $requirement = new $this->attrRequirementClass();
        $requirement->setAttribute($attribute);
        $requirement->setChannel($channel);
        $requirement->setRequired($required);

        return $requirement;
    }
}
