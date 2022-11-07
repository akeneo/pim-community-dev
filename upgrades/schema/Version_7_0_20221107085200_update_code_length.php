<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221107085200_update_code_length extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        ALTER TABLE pim_catalog_identifier_generator
        MODIFY `code` varchar(255) NOT NULL;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
