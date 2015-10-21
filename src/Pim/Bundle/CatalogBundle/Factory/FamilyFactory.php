<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;

/**
 * Family factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFactory
{
    /** @var ChannelManager */
    protected $channelManager;

    /** @var AttributeRequirementFactory */
    protected $factory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $familyClass;

    /**
     * @param ChannelManager               $channelManager
     * @param AttributeRequirementFactory  $factory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param string                       $familyClass
     */
    public function __construct(
        ChannelManager $channelManager,
        AttributeRequirementFactory $factory,
        AttributeRepositoryInterface $attributeRepository,
        $familyClass
    ) {
        $this->channelManager      = $channelManager;
        $this->factory             = $factory;
        $this->attributeRepository = $attributeRepository;
        $this->familyClass         = $familyClass;
    }

    /**
     * @return \Pim\Component\Catalog\Model\FamilyInterface
     */
    public function createFamily()
    {
        $family     = new $this->familyClass();
        $identifier = $this->attributeRepository->getIdentifier();

        $family->addAttribute($identifier);
        $family->setAttributeAsLabel($identifier);

        foreach ($this->getChannels() as $channel) {
            $requirement = $this->factory->createAttributeRequirement($identifier, $channel, true);
            $family->addAttributeRequirement($requirement);
        }

        return $family;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel[]
     */
    protected function getChannels()
    {
        return $this->channelManager->getChannels();
    }
}
