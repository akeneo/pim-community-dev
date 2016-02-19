<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\ChannelFactory;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Channel import processor
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $channelConverter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ChannelFactory */
    protected $channelFactory;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param StandardArrayConverterInterface       $channelConverter
     * @param ChannelFactory                        $channelFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $channelConverter,
        ChannelFactory $channelFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->arrayConverter = $channelConverter;
        $this->channelFactory = $channelFactory;
        $this->updater        = $updater;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->arrayConverter->convert($item);
        $channel = $this->findOrCreateChannel($convertedItem);

        try {
            $this->updater->update($channel, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($channel);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $channel;
    }

    /**
     * @param array $convertedItem
     *
     * @return ChannelInterface
     */
    protected function findOrCreateChannel(array $convertedItem)
    {
        $channel = $this->findObject($this->repository, $convertedItem);
        if (null === $channel) {
            return $this->channelFactory->create();
        }

        return $channel;
    }
}
