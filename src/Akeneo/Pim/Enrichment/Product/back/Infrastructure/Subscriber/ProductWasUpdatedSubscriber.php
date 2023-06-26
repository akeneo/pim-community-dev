<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Subscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductWasUpdatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'dispatchProductWasUpdatedMessage',
            StorageEvents::POST_SAVE_ALL => 'dispatchProductWereUpdatedMessage',
        ];
    }

    public function dispatchProductWasUpdatedMessage(GenericEvent $event): void
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (false === $unitary || !$product instanceof ProductInterface) {
            return;
        }

        // @TODO: handle ProductWasCreated
        try {
            // Launch ProductsWereUpdatedMessage with a single event to simplify the messaging stack
            $this->messageBus->dispatch(new ProductsWereUpdated([
                new ProductWasUpdated($product->getUuid(), \DateTimeImmutable::createFromMutable($product->getCreated()))
            ]));
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to dispatch ProductsWereUpdatedMessage from unitary product update', [
                'product_uuid' => $product->getUuid()->toString(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function dispatchProductWereUpdatedMessage(GenericEvent $event): void
    {
        $products = $event->getSubject();

        if (empty($products) || !reset($products) instanceof ProductInterface) {
            return;
        }

        // @TODO: handle ProductWasCreated
        try {
            $message = new ProductsWereUpdated(\array_map(
                static fn (ProductInterface $product) => new ProductWasUpdated(
                    $product->getUuid(),
                    \DateTimeImmutable::createFromMutable($product->getCreated())
                ),
                $products
            ));
            $this->messageBus->dispatch($message);
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to dispatch ProductsWereUpdatedMessage from batch products update', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
