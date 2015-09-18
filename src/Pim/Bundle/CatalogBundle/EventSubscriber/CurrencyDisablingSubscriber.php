<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Exception\LinkedChannelException;
use Pim\Bundle\CatalogBundle\Model\CurrencyInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Prevent from disabling currencies linked to channels
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyDisablingSubscriber implements EventSubscriberInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'checkChannelLink'];
    }

    /**
     * Pre remove
     *
     * @param GenericEvent $event
     */
    public function checkChannelLink(GenericEvent $event)
    {
        $object = $event->getSubject();

        if (!$object instanceof CurrencyInterface) {
            return;
        }

        if (!$object->isActivated() && 0 < $this->channelRepository->getChannelCountUsingCurrency($object)) {
            throw new LinkedChannelException('You cannot disable a currency linked to a channel.');
        }
    }
}
