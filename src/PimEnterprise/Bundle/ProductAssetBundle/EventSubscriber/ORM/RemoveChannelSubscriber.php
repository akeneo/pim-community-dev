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

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\ProductAsset\Persistence\DeleteVariationsForChannelId;
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

    /** @var DeleteVariationsForChannelId */
    protected $deleteVariationsForChannelId;

    /**
     * @todo merge master: remove the variation repository,
     *                     the variation remover,
     *                     and the "= null" of "DeleteVariationsForChannelId"
     *
     * @param VariationRepositoryInterface            $variationRepo
     * @param ChannelConfigurationRepositoryInterface $channelConfigRepo
     * @param RemoverInterface                        $variationRemover
     * @param RemoverInterface                        $channelConfigRemover
     * @param DeleteVariationsForChannelId            $deleteVariationsForChannelId
     */
    public function __construct(
        VariationRepositoryInterface $variationRepo,
        ChannelConfigurationRepositoryInterface $channelConfigRepo,
        RemoverInterface $variationRemover,
        RemoverInterface $channelConfigRemover,
        DeleteVariationsForChannelId $deleteVariationsForChannelId = null
    ) {
        $this->variationRepo = $variationRepo;
        $this->channelConfigRepo = $channelConfigRepo;
        $this->variationRemover = $variationRemover;
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
     * @todo merge master: Remove the if/else and keep only the use of "deleteVariationsForChannelId"
     *
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

        if (null !== $this->deleteVariationsForChannelId) {
            $this->deleteVariationsForChannelId->execute($channel->getId());
        } else {
            $variations = $this->variationRepo->findBy(['channel' => $channel->getId()]);
            foreach ($variations as $variation) {
                $this->variationRemover->remove($variation);
            }
        }

        $channelConfigs = $this->channelConfigRepo->findBy(['channel' => $channel->getId()]);
        foreach ($channelConfigs as $config) {
            $this->channelConfigRemover->remove($config);
        }
    }
}
