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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\FamilyAttribute\Query\FindFamilyAttributesNotInQueryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FamilySubscriber implements EventSubscriberInterface
{
    /** @var FindFamilyAttributesNotInQueryInterface */
    private $query;

    /** @var RemoveAttributesFromMappingInterface */
    private $removeAttributesFromMapping;

    /**
     * @param FindFamilyAttributesNotInQueryInterface $query
     * @param RemoveAttributesFromMappingInterface $removeAttributesFromMapping
     */
    public function __construct(
        FindFamilyAttributesNotInQueryInterface $query,
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

        $removedAttributes = $this->query->findFamilyAttributesNotIn($family->getCode(), $family->getAttributeCodes());

        $this->removeAttributesFromMapping->process([$family->getCode()], $removedAttributes);
    }
}
