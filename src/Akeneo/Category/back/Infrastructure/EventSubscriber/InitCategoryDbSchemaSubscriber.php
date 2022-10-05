<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitCategoryDbSchemaSubscriber implements EventSubscriberInterface
{
    public function __construct(private Connection $dbalConnection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $columns = $this->dbalConnection->getSchemaManager()->listTableColumns('pim_catalog_category');
        if (array_key_exists('value_collection', $columns)) {
            return;
        }

        $addCategoryValueCollectionQuery = <<<SQL
ALTER TABLE pim_catalog_category ADD value_collection JSON;
SQL;

        $this->dbalConnection->executeQuery($addCategoryValueCollectionQuery);
    }
}
