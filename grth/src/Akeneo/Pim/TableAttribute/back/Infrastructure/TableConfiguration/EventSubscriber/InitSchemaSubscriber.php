<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class InitSchemaSubscriber implements EventSubscriberInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initSchema',
        ];
    }

    public function initSchema(InstallerEvent $event): void
    {
        $sql = <<<SQL
        CREATE TABLE pim_catalog_table_column (
            id varchar(137) not null,
            attribute_id int not null,
            code varchar(100) not null,
            data_type varchar(20) not null,
            column_order int not null,
            validations json not null default ('{}'),
            labels json not null default ('{}'),
            is_required_for_completeness tinyint(1) not null
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ALTER TABLE pim_catalog_table_column
            ADD CONSTRAINT pim_catalog_table_column_pk PRIMARY KEY (id);
        ALTER TABLE pim_catalog_table_column
            ADD CONSTRAINT pim_catalog_table_column_attribute_id_fk FOREIGN KEY (attribute_id) REFERENCES pim_catalog_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE;
        ALTER TABLE pim_catalog_table_column
            ADD CONSTRAINT pim_catalog_table_column_attribute_code_unique UNIQUE (attribute_id, code);

        CREATE TABLE pim_catalog_table_column_select_option (
            column_id varchar(137) not null,
            code varchar(100) not null,
            labels json not null default ('{}')
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ALTER TABLE pim_catalog_table_column_select_option
            ADD CONSTRAINT pim_catalog_table_column_select_option_pk PRIMARY KEY (column_id, code);
        ALTER TABLE pim_catalog_table_column_select_option
            ADD CONSTRAINT pim_catalog_table_column_select_option_column_fk FOREIGN KEY (column_id) REFERENCES pim_catalog_table_column(id) ON DELETE CASCADE ON UPDATE CASCADE;
        SQL;

        $this->connection->executeQuery($sql);
    }
}
