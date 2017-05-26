<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Family factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFactory implements SimpleFactoryInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRequirementFactory */
    protected $factory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $familyClass;

    /**
     * @param ChannelRepositoryInterface               $channelRepository
     * @param AttributeRequirementFactory  $factory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param string                       $familyClass
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory,
        AttributeRepositoryInterface $attributeRepository,
        $familyClass
    ) {
        $this->channelRepository = $channelRepository;
        $this->factory = $factory;
        $this->attributeRepository = $attributeRepository;
        $this->familyClass = $familyClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        /** @var FamilyInterface $family */
        $family = new $this->familyClass();
        $identifier = $this->attributeRepository->getIdentifier();
        $supplierAttributeOption = $this->attributeRepository->findOneByIdentifier('supplier');

        $family->addAttribute($identifier);
        $family->setAttributeAsLabel($identifier);

        $family->addAttribute($supplierAttributeOption);
        $supplierChannel = $this->channelRepository->findOneByIdentifier('supplier');

        $supplierAttributeOptionRequirement = $this->factory->createAttributeRequirement($supplierAttributeOption, $supplierChannel, true);
        $family->addAttributeRequirement($supplierAttributeOptionRequirement);

        foreach ($this->getChannels() as $channel) {
            $identifierRequirement = $this->factory->createAttributeRequirement($identifier, $channel, true);
            $family->addAttributeRequirement($identifierRequirement);
        }

        return $family;
    }

    /**
     * @return array
     */
    protected function getChannels()
    {
        return $this->channelRepository->findAll();
    }
}
