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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Attribute;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeOptionUpdatedSubscriber implements EventSubscriberInterface
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
        $attributeOption = $event->getSubject();
        if (!$attributeOption instanceof AttributeOptionInterface) {
            return;
        }

        if ($event->hasArgument('unitary') && false === $event->getArgument('unitary')) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->pendingItemsRepository->addUpdatedAttributeCode($attributeOption->getAttribute()->getCode());
    }

    public function onSaveAll(GenericEvent $event): void
    {
        if ($event->hasArgument('unitary') && true === $event->getArgument('unitary')) {
            return;
        }

        $attributeOptions = $event->getSubject();
        $attributeCodes = [];
        foreach ($attributeOptions as $attributeOption) {
            if ($attributeOption instanceof AttributeOptionInterface) {
                $attributeCodes[] = $attributeOption->getAttribute()->getCode();
            }
        }

        if (empty($attributeCodes)) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        foreach ($attributeCodes as $attributeCode) {
            $this->pendingItemsRepository->addUpdatedAttributeCode($attributeCode);
        }
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
