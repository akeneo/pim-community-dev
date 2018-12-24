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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\AttributeOption;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\RemoveAttributeOptionFromMappingInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionDeletedSubscriber implements EventSubscriberInterface
{
    /** @var RemoveAttributeOptionFromMappingInterface */
    private $removeAttributeOptionsFromMapping;

    /**
     * @param RemoveAttributeOptionFromMappingInterface $removeAttributeOptionsFromMapping
     */
    public function __construct(RemoveAttributeOptionFromMappingInterface $removeAttributeOptionsFromMapping)
    {
        $this->removeAttributeOptionsFromMapping = $removeAttributeOptionsFromMapping;
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

        $this->removeAttributeOptionsFromMapping->process(
            $attributeOption->getAttribute()->getCode(),
            $attributeOption->getCode()
        );
    }
}
