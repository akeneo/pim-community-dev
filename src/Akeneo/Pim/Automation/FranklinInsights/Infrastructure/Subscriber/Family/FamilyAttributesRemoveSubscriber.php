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
    private $query;

    /** @var RemoveAttributesFromMappingInterface */
    private $removeAttributesFromMapping;

    /**
     * @param SelectRemovedFamilyAttributeCodesQueryInterface $query
     * @param RemoveAttributesFromMappingInterface $removeAttributesFromMapping
     */
    public function __construct(
        SelectRemovedFamilyAttributeCodesQueryInterface $query,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping
    ) {
        $this->query = $query;
        $this->removeAttributesFromMapping = $removeAttributesFromMapping;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'updateAttributesMapping',
        ];
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

        $removedAttributes = $this->query->execute($family->getCode(), $family->getAttributeCodes());

        $this->removeAttributesFromMapping->process([$family->getCode()], $removedAttributes);
    }
}
