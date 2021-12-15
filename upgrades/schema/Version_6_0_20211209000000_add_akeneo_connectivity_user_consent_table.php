<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20211209000000_add_akeneo_connectivity_user_consent_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS akeneo_connectivity_user_consent(
                user_id INT NOT NULL,
                app_id VARCHAR(36) NOT NULL,
                scopes JSON NOT NULL,
                uuid CHAR(36) CHARACTER SET ascii NOT NULL,
                consent_date DATETIME NOT NULL,
                PRIMARY KEY (user_id, app_id),
                CONSTRAINT FK_CONNECTIVITY_CONNECTION_user_consent_user_id FOREIGN KEY (user_id) REFERENCES oro_user (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
            SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
