<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220912132436_update_namespace_in_resource_name_for_category_history extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
UPDATE pim_versioning_version
SET resource_name='Akeneo\\\Category\\\Infrastructure\\\Component\\\Model\\\Category'
WHERE resource_name='Akeneo\\\Pim\\\Enrichment\\\Component\\\Category\\\Model\\\Category';
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
