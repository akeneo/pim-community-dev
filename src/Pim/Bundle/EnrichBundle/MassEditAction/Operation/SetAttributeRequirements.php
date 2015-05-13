<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
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
class SetAttributeRequirements extends AbstractMassEditOperation
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeRequirementFactory */
    protected $factory;

    /** @var ArrayCollection */
    protected $attRequirements;

    /** @var array */
    protected $channels;

    /** @var array */
    protected $attributes;

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
        $this->channelRepository   = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->factory             = $factory;
        $this->attRequirements     = new ArrayCollection();
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
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
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
    public function getActions()
    {
        $attrRequirements = [];

        foreach ($this->attRequirements as $attributeRequirement) {
            $attrRequirements[] = [
                'attribute_code' => $attributeRequirement->getAttribute()->getCode(),
                'channel_code'   => $attributeRequirement->getChannel()->getCode(),
                'is_required'    => $attributeRequirement->isRequired()
            ];
        }

        return $attrRequirements;
    }

    /**
     * Get the form options to configure the operation
     *
     * @return array
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'set-attribute-requirements';
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsName()
    {
        return 'family';
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'set_attribute_requirements';
    }
}
