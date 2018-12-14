<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Unsubscribe removed products.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductRemovalSubscriber implements EventSubscriberInterface
{
    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /**
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     */
    public function __construct(UnsubscribeProductHandler $unsubscribeProductHandler)
    {
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ];
    }

    /**
     * @param RemoveEvent $event
     */
    public function onPostRemove(RemoveEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $productId = $event->getSubjectId();

        try {
            $command = new UnsubscribeProductCommand($productId);
            $this->unsubscribeProductHandler->handle($command);
        } catch (ProductNotSubscribedException $e) {
            // Silently catch exception if the product is not subscribed
            // We don't check it here as the handler already check it. No need to do it twice
            return;
        }
    }
}
