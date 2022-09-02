<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Subscribers;

use Akeneo\Tool\Component\Console\CommandLauncher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RefreshRecordsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CommandLauncher $r
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
//        To activate when we improve the refresh performances
//        return [
//            ReferenceEntityRecordsDeletedEvent::class => 'onEvent',
//            AttributeOptionsDeletedEvent::class => 'onEvent',
//        ];
        return [];
    }

    public function onEvent(): void
    {
    }
}
