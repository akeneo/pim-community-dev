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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeUpdatedSubscriber implements EventSubscriberInterface
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
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if (! $this->isTypeSupported($attribute)) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->pendingItemsRepository->addUpdatedAttributeCode($attribute->getCode());
    }

    public function onSaveAll(GenericEvent $event)
    {
        $attributes = $event->getSubject();
        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            if ($attribute instanceof AttributeInterface && $this->isTypeSupported($attribute)) {
                $attributeCodes[] = $attribute->getCode();
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

    private function isTypeSupported(AttributeInterface $attribute): bool
    {
        return array_key_exists($attribute->getType(), AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS);
    }
}
