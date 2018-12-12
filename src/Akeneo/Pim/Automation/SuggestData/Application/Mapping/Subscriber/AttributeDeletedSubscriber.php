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
use Akeneo\Pim\Automation\SuggestData\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeDeletedSubscriber implements EventSubscriberInterface
{
    /** @var SelectFamilyCodesByAttributeQueryInterface */
    private $familyCodesByAttributeQuery;

    /** @var array */
    private $familyCodes = [];

    /** @var RemoveAttributesFromMappingInterface */
    private $removeAttributesFromMapping;

    /**
     * @param SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery
     * @param RemoveAttributesFromMappingInterface $removeAttributesFromMapping
     */
    public function __construct(
        SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping
    ) {
        $this->familyCodesByAttributeQuery = $familyCodesByAttributeQuery;
        $this->removeAttributesFromMapping = $removeAttributesFromMapping;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'onPreRemove',
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onPreRemove(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        $this->familyCodes = $this->familyCodesByAttributeQuery->execute($attribute->getCode());
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostRemove(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface || empty($this->familyCodes)) {
            return;
        }

        $this->removeAttributesFromMapping->process($this->familyCodes, [$attribute->getCode()]);
    }
}
