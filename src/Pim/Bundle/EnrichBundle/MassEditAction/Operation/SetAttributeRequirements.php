<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Set attribute requirements
 *
 * Applied on family grid
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetAttributeRequirements extends FamilyMassEditOperation
{
    /** @var ChannelRepository */
    protected $channelRepository;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var AttributeRequirementFactory */
    protected $factory;

    /** @var ArrayCollection */
    protected $attRequirements;

    /**
     * @param ChannelRepository           $channelRepository
     * @param AttributeRepository         $attributeRepository
     * @param AttributeRequirementFactory $factory
     */
    public function __construct(
        ChannelRepository $channelRepository,
        AttributeRepository $attributeRepository,
        AttributeRequirementFactory $factory
    ) {
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->factory = $factory;
        $this->attRequirements = new ArrayCollection();
    }

    /**
     * Get attribute requirements
     *
     * @return ArrayCollection
     */
    public function getAttributeRequirements()
    {
        return $this->attRequirements;
    }

    /**
     * Add an attribute requirement
     *
     * @param AttributeRequirement $attributeRequirement
     */
    public function addAttributeRequirement(AttributeRequirement $attributeRequirement)
    {
        if (!$this->attRequirements->contains($attributeRequirement)) {
            $this->attRequirements->set(
                sprintf(
                    '%s_%s',
                    $attributeRequirement->getAttributeCode(),
                    $attributeRequirement->getChannelCode()
                ),
                $attributeRequirement
            );
        }
    }

    /**
     * Remove an attribute requirement
     *
     * @param AttributeRequirement $attributeRequirement
     */
    public function removeAttributeRequirement(AttributeRequirement $attributeRequirement)
    {
        $this->attRequirements->removeElement($attributeRequirement);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_set_attribute_requirements';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->channels = $this->channelRepository->findAll();

        foreach ($this->attributeRepository->getNonIdentifierAttributes() as $attribute) {
            $this->attributes[(string) $attribute->getGroup()][] = $attribute;

            foreach ($this->channels as $channel) {
                $this->addAttributeRequirement(
                    $this->factory->createAttributeRequirement($attribute, $channel, false)
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(Family $family)
    {
        foreach ($this->attRequirements as $attributeRequirement) {
            $family->addAttribute($attributeRequirement->getAttribute());
            $family->addAttributeRequirement(
                $this->factory->createAttributeRequirement(
                    $attributeRequirement->getAttribute(),
                    $attributeRequirement->getChannel(),
                    $attributeRequirement->isRequired()
                )
            );
        }
    }
}
