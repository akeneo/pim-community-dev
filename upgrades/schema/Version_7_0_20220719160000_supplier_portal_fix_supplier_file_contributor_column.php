<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220719160000_supplier_portal_fix_supplier_file_contributor_column extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER table akeneo_supplier_portal_supplier_file
            MODIFY uploaded_by_contributor varchar(255) null;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
