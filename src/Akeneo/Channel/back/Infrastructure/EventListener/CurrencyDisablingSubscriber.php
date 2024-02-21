<?php

namespace Akeneo\Channel\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Exception\LinkedChannelException;
use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
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
    public static function getSubscribedEvents(): array
    {
        return [StorageEvents::PRE_SAVE => 'checkChannelLink'];
    }

    /**
     * Pre remove
     *
     * @param GenericEvent $event
     *
     * @throws LinkedChannelException
     */
    public function checkChannelLink(GenericEvent $event)
    {
        $object = $event->getSubject();

        if (!$object instanceof CurrencyInterface) {
            return;
        }

        if (null !== $object->getId() &&
            !$object->isActivated() &&
            0 < $this->channelRepository->getChannelCountUsingCurrency($object)) {
            throw new LinkedChannelException('You cannot disable a currency linked to a channel.');
        }
    }
}
