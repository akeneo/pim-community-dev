<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Family;

use InvalidArgumentException;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Applies modifications on families to add attribute requirements.
 * Used for the mass-edit operation.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetAttributeRequirements extends AbstractProcessor
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRequirementFactory */
    protected $factory;

    /**
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param ChannelRepositoryInterface          $channelRepository
     * @param AttributeRequirementFactory         $factory
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $actions = $this->getConfiguredActions();

        foreach ($actions as $action) {
            $attribute = $this->attributeRepository->findOneByIdentifier($action['attribute_code']);
            $channel = $this->channelRepository->findOneByIdentifier($action['channel_code']);
            $isRequired = $action['is_required'];

            $family->addAttribute($attribute);
            $family->addAttributeRequirement(
                $this->factory->createAttributeRequirement(
                    $attribute,
                    $channel,
                    $isRequired
                )
            );
        }

        return $family;
    }
}
