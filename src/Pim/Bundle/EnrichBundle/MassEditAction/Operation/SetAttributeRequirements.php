<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

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
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeRequirementFactory */
    protected $factory;

    /** @var ArrayCollection */
    protected $attRequirements;

    /**
     * @param ChannelRepositoryInterface   $channelRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeRequirementFactory  $factory
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
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
     * @param AttributeRequirementInterface $attributeRequirement
     */
    public function addAttributeRequirement(AttributeRequirementInterface $attributeRequirement)
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
     * @param AttributeRequirementInterface $attributeRequirement
     */
    public function removeAttributeRequirement(AttributeRequirementInterface $attributeRequirement)
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
    protected function doPerform(FamilyInterface $family)
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
