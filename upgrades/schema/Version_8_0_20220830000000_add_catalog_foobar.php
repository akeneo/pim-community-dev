<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20220830000000_add_catalog_foobar extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE akeneo_catalog
            ADD foobar JSON NOT NULL DEFAULT (JSON_OBJECT())
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
