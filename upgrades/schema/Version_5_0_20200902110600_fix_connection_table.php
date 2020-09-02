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
        if ($this->indexExists('IDX_APP_code')) {
            $this->addSql('ALTER TABLE akeneo_connectivity_connection DROP INDEX IDX_APP_code');
        }
        if ($this->indexExists('IDX_CONNECTIVITY_CONNECTION_code')) {
            $this->addSql('ALTER TABLE akeneo_connectivity_connection DROP INDEX IDX_CONNECTIVITY_CONNECTION_code');
        }
        if ($this->indexExists('FK_APP_oro_user_app_user_id')) {
            $this->addSql('ALTER TABLE akeneo_connectivity_connection DROP INDEX FK_APP_oro_user_app_user_id');
        }
        if ($this->indexExists('code')) {
            $this->addSql('ALTER TABLE akeneo_connectivity_connection DROP INDEX code');
        }
        $this->addSql('ALTER TABLE akeneo_connectivity_connection ADD PRIMARY KEY (code)');
    }

    private function fixConstraints()
    {
        if ($this->constraintExists('FK_APP_oro_user_app_user_id')) {
            $this->addSql('ALTER TABLE akeneo_connectivity_connection DROP CONSTRAINT FK_APP_oro_user_app_user_id');
        }
        if ($this->constraintExists('FK_APP_pim_api_client_app_client_id')) {
            $this->addSql('ALTER TABLE akeneo_connectivity_connection DROP CONSTRAINT FK_APP_pim_api_client_app_client_id');
        }

        if (!$this->constraintExists('FK_CONNECTIVITY_CONNECTION_client_id')) {
            $this->addSql(
                'ALTER TABLE akeneo_connectivity_connection ADD CONSTRAINT 
                FK_CONNECTIVITY_CONNECTION_client_id FOREIGN KEY (client_id) REFERENCES pim_api_client (id)'
            );
        }
        if (!$this->constraintExists('FK_CONNECTIVITY_CONNECTION_user_id')) {
            $this->addSql(
                'ALTER TABLE akeneo_connectivity_connection ADD CONSTRAINT 
                FK_CONNECTIVITY_CONNECTION_user_id FOREIGN KEY (user_id) REFERENCES oro_user (id)'
            );
        }
    }

    private function indexExists(string $index)
    {
        $rows = $this->connection->executeQuery(
            'SHOW INDEX FROM akeneo_connectivity_connection WHERE KEY_NAME = :index',
            [
                ':index' => $index,
            ]
        )->fetchAll();

        return count($rows) > 0;
    }

    private function constraintExists(string $constraint): bool
    {
        $rows = $this->connection->executeQuery(
            "SELECT table_name,column_name,referenced_table_name,referenced_column_name
            FROM information_schema.key_column_usage
            WHERE
                REFERENCED_COLUMN_NAME IS NOT NULL
                AND TABLE_NAME = 'akeneo_connectivity_connection'
                AND CONSTRAINT_NAME = :constraint",
            [
                ':constraint' => $constraint,
            ]
        )->fetchAll();

        return count($rows) > 0;
    }
}
