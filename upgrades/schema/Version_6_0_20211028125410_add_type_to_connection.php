<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211028125410_add_type_to_connection extends AbstractMigration
{
    public function up(schema $schema): void
    {
        $this->skipIf($schema->getTable('akeneo_connectivity_connection')->hasColumn('type'), 'nothing to do');

        $this->addSql("ALTER TABLE akeneo_connectivity_connection ADD type VARCHAR(30) NOT NULL DEFAULT 'default'");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
