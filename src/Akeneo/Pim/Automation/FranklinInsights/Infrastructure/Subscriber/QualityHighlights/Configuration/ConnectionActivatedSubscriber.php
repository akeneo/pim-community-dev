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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Configuration;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Event\ConnectionActivated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConnectionActivatedSubscriber implements EventSubscriberInterface
{
    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    public function __construct(PendingItemsRepositoryInterface $pendingItemsRepository)
    {
        $this->pendingItemsRepository = $pendingItemsRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConnectionActivated::EVENT_NAME => 'fillPendingItems',
        ];
    }

    public function fillPendingItems(ConnectionActivated $event)
    {
        $this->pendingItemsRepository->fillWithAllAttributes();
        $this->pendingItemsRepository->fillWithAllFamilies();
        $this->pendingItemsRepository->fillWithAllProducts();
    }
}
