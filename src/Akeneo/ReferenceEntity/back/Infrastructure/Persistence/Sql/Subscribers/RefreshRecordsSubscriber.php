<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Subscribers;

use Akeneo\ReferenceEntity\Domain\Event\AttributeOptionsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\CLI\RefreshRecordsCommand;
use Akeneo\Tool\Component\Console\CommandLauncher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RefreshRecordsSubscriber implements EventSubscriberInterface
{
    /** @var CommandLauncher  */
    private $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
//        To activate when we improve the refresh performances
//        return [
//            RecordDeletedEvent::class => 'onEvent',
//            ReferenceEntityRecordsDeletedEvent::class => 'onEvent',
//            AttributeOptionsDeletedEvent::class => 'onEvent',
//        ];
        return [];
    }

    public function onEvent(): void
    {
    }
}
