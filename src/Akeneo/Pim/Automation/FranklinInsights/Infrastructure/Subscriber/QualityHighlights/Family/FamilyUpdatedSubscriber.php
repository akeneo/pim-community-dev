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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilyUpdatedSubscriber implements EventSubscriberInterface
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
        $family = $event->getSubject();
        if (!$family instanceof FamilyInterface) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->pendingItemsRepository->addUpdatedFamilyCode($family->getCode());
    }

    public function onSaveAll(GenericEvent $event)
    {
        $families = $event->getSubject();
        $familyCodes = [];
        foreach ($families as $family) {
            if ($family instanceof FamilyInterface) {
                $familyCodes[] = $family->getCode();
            }
        }

        if (empty($familyCodes)) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        foreach ($familyCodes as $familyCode) {
            $this->pendingItemsRepository->addUpdatedFamilyCode($familyCode);
        }
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
