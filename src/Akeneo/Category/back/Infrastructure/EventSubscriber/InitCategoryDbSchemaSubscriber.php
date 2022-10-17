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

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $this->addValueCollectionInCategoryTable();
        $this->addCategoryTemplateTable();
    }

    private function addValueCollectionInCategoryTable()
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

    private function addCategoryTemplateTable()
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS pim_catalog_category_template (
    uuid binary(16) PRIMARY KEY,
    code VARCHAR(100) NOT NULL,
    labels JSON NOT NULL DEFAULT ('{}')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->dbalConnection->executeQuery($query);
    }
}
