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
        $this->addCategoryTreeTemplateTable();
        $this->addCategoryTemplateUuidColumn();
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

    private function addCategoryTreeTemplateTable()
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `pim_catalog_category_tree_template` (
                `category_tree_id` int NOT NULL,  
                `category_template_uuid` binary(16) NOT NULL,
                CONSTRAINT `FK_TREE_TEMPLATE_template_uuid` FOREIGN KEY (`category_template_uuid`) REFERENCES `pim_catalog_category_template` (`uuid`),
                CONSTRAINT `FK_TREE_TEMPLATE_tree_id` FOREIGN KEY (`category_tree_id`) REFERENCES `pim_catalog_category` (`id`),
                CONSTRAINT `PRIMARY` PRIMARY KEY (category_tree_id,category_template_uuid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        SQL;

        $this->dbalConnection->executeQuery($query);
    }

    private function addCategoryTemplateUuidColumn()
    {
        $query = <<<SQL
            ALTER TABLE pim_catalog_category ADD category_template_uuid binary(16) NULL;
            ALTER TABLE pim_catalog_category ADD CONSTRAINT FK_CATEGORY_template_uuid FOREIGN KEY (category_template_uuid) REFERENCES pim_catalog_category_template(uuid);
        SQL;

        $this->dbalConnection->executeQuery($query);
    }
}
