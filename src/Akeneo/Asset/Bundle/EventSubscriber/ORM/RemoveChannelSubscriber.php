<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\EventSubscriber\ORM;

use Akeneo\Asset\Component\Query\DeleteVariationsForChannelId;
use Akeneo\Asset\Component\Repository\ChannelConfigurationRepositoryInterface;
use Akeneo\Asset\Component\Repository\VariationRepositoryInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveChannelSubscriber implements EventSubscriberInterface
{
    /** @var ChannelConfigurationRepositoryInterface */
    protected $channelConfigRepo;

    /** @var RemoverInterface */
    protected $channelConfigRemover;

    /** @var DeleteVariationsForChannelId */
    protected $deleteVariationsForChannelId;

    /**
     * @param ChannelConfigurationRepositoryInterface $channelConfigRepo
     * @param RemoverInterface                        $channelConfigRemover
     * @param DeleteVariationsForChannelId            $deleteVariationsForChannelId
     */
    public function __construct(
        ChannelConfigurationRepositoryInterface $channelConfigRepo,
        RemoverInterface $channelConfigRemover,
        DeleteVariationsForChannelId $deleteVariationsForChannelId
    ) {
        $this->channelConfigRepo = $channelConfigRepo;
        $this->channelConfigRemover = $channelConfigRemover;
        $this->deleteVariationsForChannelId = $deleteVariationsForChannelId;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeChannelLinkedEntities',
        ];
    }

    /**
     * @param GenericEvent $event
     *
     * @throw \InvalidArgumentException
     */
    public function removeChannelLinkedEntities(GenericEvent $event)
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $this->deleteVariationsForChannelId->execute($channel->getId());

        $channelConfigs = $this->channelConfigRepo->findBy(['channel' => $channel->getId()]);
        foreach ($channelConfigs as $config) {
            $this->channelConfigRemover->remove($config);
        }
    }
}
