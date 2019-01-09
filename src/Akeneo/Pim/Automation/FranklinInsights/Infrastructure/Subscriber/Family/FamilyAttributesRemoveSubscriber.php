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

    /**
     * @param SelectRemovedFamilyAttributeCodesQueryInterface $selectRemovedFamilyAttributeCodesQuery
     * @param RemoveAttributesFromMappingInterface $removeAttributesFromMapping
     */
    public function __construct(
        SelectRemovedFamilyAttributeCodesQueryInterface $selectRemovedFamilyAttributeCodesQuery,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping
    ) {
        $this->selectRemovedFamilyAttributeCodesQuery = $selectRemovedFamilyAttributeCodesQuery;
        $this->removeAttributesFromMapping = $removeAttributesFromMapping;
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
}
