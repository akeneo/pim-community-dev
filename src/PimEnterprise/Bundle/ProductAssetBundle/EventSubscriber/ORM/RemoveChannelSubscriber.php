<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ORM;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Channel\Component\Model\ChannelInterface;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveChannelSubscriber implements EventSubscriberInterface
{
    /** @var VariationRepositoryInterface */
    protected $variationRepo;

    /** @var RemoverInterface */
    protected $variationRemover;

    /** @var ChannelConfigurationRepositoryInterface */
    protected $channelConfigRepo;

    /** @var RemoverInterface */
    protected $channelConfigRemover;

    /**
     * @param VariationRepositoryInterface            $variationRepo
     * @param ChannelConfigurationRepositoryInterface $channelConfigRepo
     * @param RemoverInterface                        $variationRemover
     * @param RemoverInterface                        $channelConfigRemover
     */
    public function __construct(
        VariationRepositoryInterface $variationRepo,
        ChannelConfigurationRepositoryInterface $channelConfigRepo,
        RemoverInterface $variationRemover,
        RemoverInterface $channelConfigRemover
    ) {
        $this->variationRepo = $variationRepo;
        $this->channelConfigRepo = $channelConfigRepo;
        $this->variationRemover = $variationRemover;
        $this->channelConfigRemover = $channelConfigRemover;
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

        $variations = $this->variationRepo->findBy(['channel' => $channel->getId()]);
        foreach ($variations as $variation) {
            $this->variationRemover->remove($variation);
        }

        $channelConfigs = $this->channelConfigRepo->findBy(['channel' => $channel->getId()]);
        foreach ($channelConfigs as $config) {
            $this->channelConfigRemover->remove($config);
        }
    }
}
