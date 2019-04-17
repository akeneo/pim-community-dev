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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Unsubscribe removed products.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductRemoveSubscriber implements EventSubscriberInterface
{
    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /**
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     * @param GetConnectionStatusHandler $connectionStatusHandler
     */
    public function __construct(
        UnsubscribeProductHandler $unsubscribeProductHandler,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->connectionStatusHandler = $connectionStatusHandler;
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

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $productId = new ProductId($event->getSubjectId());
        $command = new UnsubscribeProductCommand($productId);

        try {
            $this->unsubscribeProductHandler->handle($command);
        } catch (ProductNotSubscribedException $e) {
            // Silently catch exception if the product is not subscribed
            // We don't check it here as the handler already check it. No need to do it twice
            return;
        }
    }

    /**
     * @return bool
     */
    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
