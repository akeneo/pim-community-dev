<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BlacklistAttributeCodeOnAttributeDeletion implements EventSubscriberInterface
{
    private AttributeCodeBlacklister $attributeCodeBlacklister;

    public function __construct(AttributeCodeBlacklister $attributeCodeBlacklister)
    {
        $this->attributeCodeBlacklister = $attributeCodeBlacklister;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_REMOVE => 'blacklistAttributeCodeOnAttributeDeletion',
        ];
    }

    public function blacklistAttributeCodeOnAttributeDeletion(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof AttributeInterface) {
            return;
        }

        $this->attributeCodeBlacklister->blacklist($subject->getCode());
    }
}
