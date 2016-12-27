<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Family;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param ChannelRepositoryInterface          $channelRepository
     * @param AttributeRequirementFactory         $factory
     * @param ValidatorInterface                  $validator
     * @param ObjectDetacherInterface             $detacher
     */
    public function __construct(
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $factory,
        ValidatorInterface $validator = null,
        ObjectDetacherInterface $detacher = null
    ) {
        parent::__construct($jobConfigurationRepo);

        $this->attributeRepository = $attributeRepository;
        $this->channelRepository   = $channelRepository;
        $this->factory             = $factory;
        $this->validator           = $validator;
        $this->detacher            = $detacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $configuration = $this->getJobConfiguration();

        if (!array_key_exists('actions', $configuration)) {
            throw new InvalidArgumentException('Missing configuration for \'actions\'.');
        }

        $actions = $configuration['actions'];

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

        if (null !== $this->validator) {
            $violations = $this->validator->validate($family);

            if (0 !== $violations->count()) {
                foreach ($violations as $violation) {
                    $errors = sprintf("Family %s: %s\n", (string) $family, $violation->getMessage());
                    $this->stepExecution->addWarning($this->getName(), $errors, [], $family);
                }

                $this->stepExecution->incrementSummaryInfo('skipped_families');
                $this->detacher->detach($family);

                return null;
            }
        }

        return $family;
    }
}
