<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
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

class ProductUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    public function __construct(GetConnectionStatusHandler $connectionStatusHandler, PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->connectionStatusHandler = $connectionStatusHandler;
        $this->pendingItemsRepository = $pendingItemsRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'onSave',
            StorageEvents::POST_SAVE_ALL => 'onSaveAll',
        ];
    }

    public function onSave(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->pendingItemsRepository->addUpdatedProductIdentifier($product->getId());
    }

    public function onSaveAll(GenericEvent $event)
    {
        $products = $event->getSubject();
        $productCodes = [];
        foreach ($products as $product) {
            if ($product instanceof ProductInterface) {
                $productCodes[] = $product->getId();
            }
        }

        if (empty($productCodes)) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        foreach ($productCodes as $productCode) {
            $this->pendingItemsRepository->addUpdatedProductIdentifier($productCode);
        }
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
