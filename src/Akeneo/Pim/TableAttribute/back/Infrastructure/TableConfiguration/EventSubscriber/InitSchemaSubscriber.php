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
        create table pim_catalog_table_column (
            id varchar(137) not null,
            attribute_id int not null,
            code varchar(100) not null,
            data_type varchar(20) not null,
            column_order int not null,
            validations json not null default ('{}'),
            labels json not null default ('{}')
        );
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_pk primary key (id);
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_attribute_id_fk foreign key (attribute_id) REFERENCES pim_catalog_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE;
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_attribute_code_unique unique (attribute_id, code);
        alter table pim_catalog_table_column add constraint pim_catalog_table_column_attribute_order_unique unique (attribute_id, column_order);
        SQL;

        $this->connection->executeQuery($sql);
    }
}
