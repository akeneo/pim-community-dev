<?php

namespace Akeneo\Pim\Structure\Component\Processor\MassEdit;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ChannelRepositoryInterface   $channelRepository
     * @param AttributeRequirementFactory  $factory
     * @param ValidatorInterface           $validator
     * @param ObjectDetacherInterface      $detacher
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
        $this->factory = $factory;
        $this->validator = $validator;
        $this->detacher = $detacher;
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

        $violations = $this->validator->validate($family);

        if (0 !== $violations->count()) {
            $this->addWarningMessage($violations, $family);

            $this->stepExecution->incrementSummaryInfo('skipped_families');
            $this->detacher->detach($family);

            return null;
        }

        return $family;
    }
}
