<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20220524122443_oro_user_field_consecutive_authentication_failure_counter_is_not_nullable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'To be consistent with code and schema description the column oro_user.consecutive_authentication_failure_counter must not accept NULL value';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            $this->isMigrationAlreadyApplied($schema),
            'Column oro_user.consecutive_authentication_failure_counter is already INT UNSIGNED NOT NULL DEFAULT 0'
        );

        // should not be any null in this column, but have to be 100% sure
        $this->addSql(<<<SQL
        UPDATE oro_user
        SET consecutive_authentication_failure_counter=0
        WHERE ISNULL(consecutive_authentication_failure_counter)
        SQL);

        $this->addSql(<<<SQL
        ALTER TABLE oro_user
            MODIFY COLUMN consecutive_authentication_failure_counter
                INT UNSIGNED NOT NULL DEFAULT 0
        SQL);
    }

    private function isMigrationAlreadyApplied(Schema $schema): bool
    {
        $sql = <<<SQL
        SELECT IS_NULLABLE, COLUMN_TYPE , COLUMN_DEFAULT
        FROM information_schema.columns
        WHERE TABLE_SCHEMA=table_schema 
          AND TABLE_NAME='oro_user'
          AND COLUMN_NAME ='consecutive_authentication_failure_counter'
        SQL;

        $result = $this->connection->executeQuery($sql, [
            'table_schema' => $schema->getName()
        ])->fetchAssociative();

        return $result['IS_NULLABLE'] === 'NO'
            && $result['COLUMN_TYPE'] === 'int unsigned'
            && $result['COLUMN_DEFAULT'] === '0';
    }


    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
