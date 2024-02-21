<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version_7_0_20220701154545_add_app_id_to_user_properties extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            UPDATE oro_user
                JOIN akeneo_connectivity_connection ON oro_user.id = akeneo_connectivity_connection.user_id
                JOIN akeneo_connectivity_connected_app ON akeneo_connectivity_connection.code = akeneo_connectivity_connected_app.connection_code
            SET oro_user.properties = CASE
                WHEN oro_user.properties != JSON_ARRAY() THEN JSON_INSERT(oro_user.properties, '$.app_id', akeneo_connectivity_connected_app.id)
                WHEN oro_user.properties = JSON_ARRAY() THEN JSON_OBJECT('app_id', akeneo_connectivity_connected_app.id)
                END
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
