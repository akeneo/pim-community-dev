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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Family;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectRemovedFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FamilyAttributesRemoveSubscriber implements EventSubscriberInterface
{
    /** @var SelectRemovedFamilyAttributeCodesQueryInterface */
    private $selectRemovedFamilyAttributeCodesQuery;

    /** @var RemoveAttributesFromMappingInterface */
    private $removeAttributesFromMapping;

    /** @var string[] */
    private $removedAttributeCodes;

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /**
     * @param SelectRemovedFamilyAttributeCodesQueryInterface $selectRemovedFamilyAttributeCodesQuery
     * @param RemoveAttributesFromMappingInterface $removeAttributesFromMapping
     * @param GetConnectionStatusHandler $connectionStatusHandler
     */
    public function __construct(
        SelectRemovedFamilyAttributeCodesQueryInterface $selectRemovedFamilyAttributeCodesQuery,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        $this->selectRemovedFamilyAttributeCodesQuery = $selectRemovedFamilyAttributeCodesQuery;
        $this->removeAttributesFromMapping = $removeAttributesFromMapping;
        $this->connectionStatusHandler = $connectionStatusHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'onFamilyAttributesRemoved',
            StorageEvents::POST_SAVE => 'updateAttributesMapping',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onFamilyAttributesRemoved(GenericEvent $event): void
    {
        $family = $event->getSubject();
        if (!$family instanceof FamilyInterface || null === $family->getId()) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->removedAttributeCodes = $this->selectRemovedFamilyAttributeCodesQuery->execute(
            $family->getCode(),
            $family->getAttributeCodes()
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function updateAttributesMapping(GenericEvent $event): void
    {
        $family = $event->getSubject();
        if (!$family instanceof FamilyInterface || null === $family->getId()) {
            return;
        }

        if (empty($this->removedAttributeCodes)) {
            return;
        }

        $this->removeAttributesFromMapping->process([$family->getCode()], $this->removedAttributeCodes);
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
