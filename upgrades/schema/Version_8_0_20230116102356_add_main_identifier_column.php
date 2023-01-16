<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230116102356_add_main_identifier_column extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the is_main_identifier column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pim_catalog_attribute ADD main_identifier TINYINT(1) NOT NULL;');
        $this->addSql(
            'UPDATE pim_catalog_attribute SET main_identifier = TRUE WHERE code = \'sku\';'
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
