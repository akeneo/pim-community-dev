<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Family factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFactory
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var AttributeRequirementFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param ProductManager              $productManager
     * @param ChannelManager              $channelManager
     * @param AttributeRequirementFactory $factory
     */
    public function __construct(
        ProductManager $productManager,
        ChannelManager $channelManager,
        AttributeRequirementFactory $factory
    ) {
        $this->productManager = $productManager;
        $this->channelManager = $channelManager;
        $this->factory        = $factory;
    }

    /**
     * Create and configure a family intance
     *
     * @return Family
     */
    public function createFamily()
    {
        $family     = new Family();
        $identifier = $this->productManager->getIdentifierAttribute();

        $family->addAttribute($identifier);
        $family->setAttributeAsLabel($identifier);

        foreach ($this->getChannels() as $channel) {
            $family->addAttributeRequirement(
                $this->factory->createAttributeRequirement($identifier, $channel, true)
            );
        }

        return $family;
    }

    /**
     * Get the PIM channels
     *
     * @return Pim\Bundle\CatalogBundle\Entity\Channel[]
     */
    protected function getChannels()
    {
        return $this->channelManager->getChannels();
    }
}
