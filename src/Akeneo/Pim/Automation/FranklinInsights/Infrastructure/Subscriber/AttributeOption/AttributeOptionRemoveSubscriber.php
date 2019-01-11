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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\AttributeOption;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\RemoveAttributeOptionFromMappingInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionRemoveSubscriber implements EventSubscriberInterface
{
    /** @var RemoveAttributeOptionFromMappingInterface */
    private $removeAttributeOptionsFromMapping;

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /**
     * @param RemoveAttributeOptionFromMappingInterface $removeAttributeOptionsFromMapping
     * @param GetConnectionStatusHandler $connectionStatusHandler
     */
    public function __construct(
        RemoveAttributeOptionFromMappingInterface $removeAttributeOptionsFromMapping,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        $this->removeAttributeOptionsFromMapping = $removeAttributeOptionsFromMapping;
        $this->connectionStatusHandler = $connectionStatusHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'removeAttributeOptionFromMapping',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function removeAttributeOptionFromMapping(GenericEvent $event): void
    {
        $attributeOption = $event->getSubject();
        if (!$attributeOption instanceof AttributeOptionInterface) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->removeAttributeOptionsFromMapping->process(
            $attributeOption->getAttribute()->getCode(),
            $attributeOption->getCode()
        );
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
