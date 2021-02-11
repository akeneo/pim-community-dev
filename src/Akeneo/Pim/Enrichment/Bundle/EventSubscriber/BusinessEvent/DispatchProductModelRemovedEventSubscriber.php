<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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
final class DispatchProductModelRemovedEventSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private MessageBusInterface $messageBus;
    private int $maxBulkSize;
    private LoggerInterface $logger;

    /** @var array<ProductModelRemoved> */
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
            StorageEvents::POST_REMOVE => 'createAndDispatchProductModelEvents',
            StorageEvents::POST_SAVE_ALL => 'dispatchBufferedProductModelEvents',
        ];
    }

    public function createAndDispatchProductModelEvents(GenericEvent $postSaveEvent): void
    {
        /** @var ProductModelInterface */
        $productModel = $postSaveEvent->getSubject();
        if (false === $productModel instanceof ProductModelInterface) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            return;
        }

        $author = Author::fromUser($user);
        $data = [
            'code' => $productModel->getCode(),
            'category_codes' => $productModel->getCategoryCodes(),
        ];

        $this->events[] = new ProductModelRemoved($author, $data);

        if ($postSaveEvent->hasArgument('unitary') && true === $postSaveEvent->getArgument('unitary')) {
            $this->dispatchBufferedProductModelEvents();
        } elseif (count($this->events) >= $this->maxBulkSize) {
            $this->dispatchBufferedProductModelEvents();
        }
    }

    public function dispatchBufferedProductModelEvents(): void
    {
        if (count($this->events) === 0) {
            return;
        }

        try {
            $this->messageBus->dispatch(new BulkEvent($this->events));
            foreach ($this->events as $event) {
                $this->logger->info(
                    json_encode(
                        [
                            'type' => $event->getName(),
                            'uuid' => $event->getUuid(),
                            'author' => $event->getAuthor()->name(),
                            'author_type' => $event->getAuthor()->type(),
                            'timestamp' => $event->getTimestamp(),
                        ],
                        JSON_THROW_ON_ERROR
                    )
                );
            }
        } catch (TransportException $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->events = [];
    }
}
