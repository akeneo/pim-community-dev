<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Domain\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\IndexRecordsCommand;
use Akeneo\Tool\Component\Console\CommandLauncher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordSubscriber implements EventSubscriberInterface
{
    /** @var RecordIndexerInterface */
    private $recordIndexer;

    /** @var CommandLauncher */
    private $commandLauncher;

    public function __construct(
        RecordIndexerInterface $recordIndexer,
        CommandLauncher $commandLauncher
    ) {
        $this->recordIndexer = $recordIndexer;
        $this->commandLauncher = $commandLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RecordUpdatedEvent::class    => 'whenRecordUpdated',
            AttributeDeletedEvent::class => 'whenAttributeIsDeleted',
        ];
    }

    public function whenRecordUpdated(RecordUpdatedEvent $recordUpdatedEvent): void
    {
        $this->recordIndexer->index($recordUpdatedEvent->getRecordIdentifier());
    }

    public function whenAttributeIsDeleted(AttributeDeletedEvent $attributeDeletedEvent): void
    {
        $cmd = sprintf(
            '%s %s',
            IndexRecordsCommand::INDEX_RECORDS_COMMAND_NAME,
            (string) $attributeDeletedEvent->referenceEntityIdentifier
        );

        $this->commandLauncher->executeBackground($cmd, '/dev/null');
    }
}
