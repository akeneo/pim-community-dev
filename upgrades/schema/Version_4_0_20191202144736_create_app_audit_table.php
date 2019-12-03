<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will create the app audit table.
 *
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20191202144736_create_app_audit_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $createTableQuery = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_app_audit(
    id INT NOT NULL AUTO_INCREMENT,
    app_code VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    count INT NOT NULL,
    event_type ENUM('product_created', 'product_updated') NOT NULL,
    updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_AUDIT_akeneo_app_audit_code FOREIGN KEY (app_code) REFERENCES akeneo_app (code),
    INDEX IDX_AUDIT_id (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;

        $this->addSql($createTableQuery);
    }

    public function down(Schema $schema) : void
    {
        $dropTableQuery = <<<SQL
DROP TABLE akeneo_app_audit
SQL;

        $this->addSql($dropTableQuery);
    }
}
