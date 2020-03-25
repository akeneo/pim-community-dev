<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the Rule Definition translations table
 */
final class Version_5_0_20200325080039_create_rule_definition_translation_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql(<<<SQL
CREATE TABLE akeneo_rule_engine_rule_definition_translation (
    id INT AUTO_INCREMENT NOT NULL,
    foreign_key INT DEFAULT NULL,
    label VARCHAR(100) DEFAULT NULL,
    locale VARCHAR(20) NOT NULL,
    INDEX IDX_C0477CAA7E366551 (foreign_key),
    UNIQUE INDEX locale_foreign_key_idx (locale, foreign_key),
    PRIMARY KEY(id)
) 
DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL
        );
        $this->addSql(<<<SQL
ALTER TABLE akeneo_rule_engine_rule_definition_translation 
ADD CONSTRAINT FK_C0477CAA7E366551 
FOREIGN KEY (foreign_key)
REFERENCES akeneo_rule_engine_rule_definition (id)
ON DELETE CASCADE
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
