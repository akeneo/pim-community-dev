<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200623071353_rule_translation_label_length extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(
            'ALTER TABLE akeneo_rule_engine_rule_definition_translation MODIFY COLUMN label VARCHAR(255)'
        );
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
