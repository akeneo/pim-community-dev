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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyDeletedSubscriber implements EventSubscriberInterface
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
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ];
    }

    public function onPostRemove(GenericEvent $event): void
    {
        $family = $event->getSubject();
        if (!$family instanceof FamilyInterface) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->pendingItemsRepository->addDeletedFamilyCode($family->getCode());
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
