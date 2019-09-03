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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class ProductDeletedSubscriber implements EventSubscriberInterface
{
    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    /** @var int */
    private $removedProductId;

    public function __construct(GetConnectionStatusHandler $connectionStatusHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->connectionStatusHandler = $connectionStatusHandler;
        $this->pendingItemsRepository = $pendingItemsRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'onPreRemove',
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ];
    }

    public function onPreRemove(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface || $product->isVariant()) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->removedProductId = $product->getId();
    }

    public function onPostRemove(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface || $product->isVariant()) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->pendingItemsRepository->addDeletedProductId($this->removedProductId);
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
