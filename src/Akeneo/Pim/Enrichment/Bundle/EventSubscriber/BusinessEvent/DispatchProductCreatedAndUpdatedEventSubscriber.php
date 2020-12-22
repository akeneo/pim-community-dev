<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchProductCreatedAndUpdatedEventSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private MessageBusInterface $messageBus;
    private int $maxBulkSize;
    private LoggerInterface $logger;

    /** @var array<ProductCreated|ProductUpdated> */
    private array $events = [];

    public function __construct(
        Security $security,
        MessageBusInterface $messageBus,
        int $maxBulkSize,
        LoggerInterface $logger
    ) {
        $this->security = $security;
        $this->messageBus = $messageBus;
        $this->maxBulkSize = $maxBulkSize;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'createAndDispatchProductEvents',
            StorageEvents::POST_SAVE_ALL => 'dispatchBufferedProductEvents',
        ];
    }

    public function createAndDispatchProductEvents(GenericEvent $postSaveEvent): void
    {
        /** @var ProductInterface */
        $product = $postSaveEvent->getSubject();
        if (false === $product instanceof ProductInterface) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            return;
        }

        $author = Author::fromUser($user);
        $data = [
            'identifier' => $product->getIdentifier(),
        ];

        if ($postSaveEvent->hasArgument('is_new') && true === $postSaveEvent->getArgument('is_new')) {
            $this->events[] = new ProductCreated($author, $data);
        } else {
            $this->events[] = new ProductUpdated($author, $data);
        }

        if ($postSaveEvent->hasArgument('unitary') && true === $postSaveEvent->getArgument('unitary')) {
            $this->dispatchBufferedProductEvents();
        } elseif (count($this->events) >= $this->maxBulkSize) {
            $this->dispatchBufferedProductEvents();
        }
    }

    public function dispatchBufferedProductEvents(): void
    {
        if (count($this->events) === 0) {
            return;
        }

        try {
            $this->messageBus->dispatch(new BulkEvent($this->events));
        } catch (TransportException $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->events = [];
    }
}
