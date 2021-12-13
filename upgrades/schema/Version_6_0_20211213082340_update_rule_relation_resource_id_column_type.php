<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Update column type of akeneo_rule_engine_rule_relation.resource_id from VARCHAR to INT
 */
final class Version_6_0_20211213082340_update_rule_relation_resource_id_column_type extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update column type of akeneo_rule_engine_rule_relation.resource_id from VARCHAR to INT';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE akeneo_rule_engine_rule_relation MODIFY resource_id INT NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
