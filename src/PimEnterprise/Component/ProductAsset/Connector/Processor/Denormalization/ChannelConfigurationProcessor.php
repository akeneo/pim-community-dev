<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Component\ProductAsset\Factory\ChannelConfigurationFactory;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Denormalize a ChannelVariationsConfiguration
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ChannelConfigurationProcessor extends AbstractProcessor implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var ChannelConfigurationFactory */
    protected $configurationFactory;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param ChannelConfigurationFactory           $configurationFactory
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        ChannelConfigurationFactory $configurationFactory,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->channelRepository      = $channelRepository;
        $this->configurationFactory   = $configurationFactory;
        $this->validator              = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $channelConfiguration = $this->findOrCreateChannelConfiguration($item);

        try {
            $this->updateChannelConfiguration($channelConfiguration, $item);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);

            return null;
        }

        $violations = $this->validateChannelConfiguration($channelConfiguration);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $channelConfiguration;
    }

    /**
     * Find or create the asset channel configuration
     *
     * @param array $convertedItem
     *
     * @return ChannelVariationsConfigurationInterface
     */
    protected function findOrCreateChannelConfiguration(array $convertedItem)
    {
        $channelCode = $convertedItem['channel'];
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw new \InvalidArgumentException(sprintf('Channel "%s" does not exists', $channelCode));
        }

        $channelConfiguration = $this->repository->findOneByIdentifier($channel);
        if (null === $channelConfiguration) {
            $channelConfiguration = $this->configurationFactory->createChannelConfiguration();
            $channelConfiguration->setChannel($channel);
        }

        return $channelConfiguration;
    }

    /**
     * Update the asset channel configuration fields
     *
     * @param ChannelVariationsConfigurationInterface $channelConfiguration
     * @param array                                   $convertedItem
     */
    protected function updateChannelConfiguration(
        ChannelVariationsConfigurationInterface $channelConfiguration,
        array $convertedItem
    ) {
        $channelConfiguration->setConfiguration($convertedItem['configuration']);
    }

    /**
     * @param ChannelVariationsConfigurationInterface $channelConfiguration
     *
     * @throws InvalidItemException
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateChannelConfiguration(
        ChannelVariationsConfigurationInterface $channelConfiguration
    ) {
        return $this->validator->validate($channelConfiguration);
    }
}
