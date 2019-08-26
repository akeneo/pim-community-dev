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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingAttributesRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeDeletedSubscriber implements EventSubscriberInterface
{
    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /** @var PendingAttributesRepositoryInterface */
    private $pendingAttributesRepository;

    private $attributeIdToDelete;

    public function __construct(GetConnectionStatusHandler $connectionStatusHandler, PendingAttributesRepositoryInterface $pendingAttributesRepository)
    {
        $this->connectionStatusHandler = $connectionStatusHandler;
        $this->pendingAttributesRepository = $pendingAttributesRepository;
        $this->attributeIdToDelete = null;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'onPreDelete',
            StorageEvents::POST_REMOVE => 'onPostDelete',
        ];
    }

    public function onPreDelete(GenericEvent $event)
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->attributeIdToDelete = (int) $attribute->getId();
    }

    public function onPostDelete(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        if ($this->attributeIdToDelete !== null) {
            $this->pendingAttributesRepository->addDeletedAttributeId($this->attributeIdToDelete);
        }
    }

    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
