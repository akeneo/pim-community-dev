<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200902110600_fix_connection_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->fixIndexes();
        $this->fixConstraints();
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function fixIndexes()
    {
        $this->addSql(<<<SQL
IF EXISTS
(
    SELECT *
    FROM information_schema.statistics
    WHERE TABLE_NAME='akeneo_connectivity_connection' AND INDEX_NAME = 'IDX_APP_code'
)
THEN
    ALTER TABLE akeneo_connectivity_connection DROP INDEX IDX_APP_code;
END IF;
SQL
        );

        $this->addSql(<<<SQL
IF EXISTS
(
    SELECT *
    FROM information_schema.statistics
    WHERE TABLE_NAME='akeneo_connectivity_connection' AND INDEX_NAME = 'FK_APP_oro_user_app_user_id'
)
THEN
    ALTER TABLE akeneo_connectivity_connection DROP INDEX FK_APP_oro_user_app_user_id;
END IF;
SQL
        );

        $this->addSql(<<<SQL
IF NOT EXISTS
(
    SELECT *
    FROM information_schema.statistics
    WHERE TABLE_NAME='akeneo_connectivity_connection' AND INDEX_NAME = 'code'
)
THEN
    ALTER TABLE akeneo_connectivity_connection ADD PRIMARY KEY (code);
END IF;
SQL
        );
    }

    private function fixConstraints()
    {
        $this->addSql(<<<SQL
IF EXISTS
(
    SELECT *
    FROM information_schema.statistics
    WHERE TABLE_NAME='akeneo_connectivity_connection' AND CONSTRAINT_NAME = 'FK_APP_oro_user_app_user_id'
)
THEN
    ALTER TABLE akeneo_connectivity_connection DROP CONSTRAINT FK_APP_oro_user_app_user_id;
END IF;
SQL
        );

        $this->addSql(<<<SQL
IF EXISTS
(
    SELECT *
    FROM information_schema.statistics
    WHERE TABLE_NAME='akeneo_connectivity_connection' AND CONSTRAINT_NAME = 'FK_APP_pim_api_client_app_client_id'
)
THEN
    ALTER TABLE akeneo_connectivity_connection DROP CONSTRAINT FK_APP_pim_api_client_app_client_id;
END IF;
SQL
        );

        $this->addSql(<<<SQL
IF NOT EXISTS
(
    SELECT *
    FROM information_schema.statistics
    WHERE TABLE_NAME='akeneo_connectivity_connection' AND CONSTRAINT_NAME = 'FK_CONNECTIVITY_CONNECTION_client_id'
)
THEN
    ALTER TABLE akeneo_connectivity_connection 
        ADD CONSTRAINT FK_CONNECTIVITY_CONNECTION_client_id FOREIGN KEY (client_id) REFERENCES pim_api_client (id);
END IF;
SQL
        );

        $this->addSql(<<<SQL
IF NOT EXISTS
(
    SELECT *
    FROM information_schema.statistics
    WHERE TABLE_NAME='akeneo_connectivity_connection' AND CONSTRAINT_NAME = 'FK_CONNECTIVITY_CONNECTION_user_id'
)
THEN
    ALTER TABLE akeneo_connectivity_connection 
        ADD CONSTRAINT FK_CONNECTIVITY_CONNECTION_user_id FOREIGN KEY (user_id) REFERENCES oro_user (id);
END IF;
SQL
        );
    }
}
