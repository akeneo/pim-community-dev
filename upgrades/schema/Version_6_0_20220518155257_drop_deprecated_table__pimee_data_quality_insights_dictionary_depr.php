<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version_6_0_20220518155257_drop_deprecated_table__pimee_data_quality_insights_dictionary_depr extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove deprecated table pimee_data_quality_insights_dictionary_depr. It was used in migration Version_6_0_20220221151255_data_quality_insights_purge_attribute_quality but is no longer needed.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
DROP TABLE IF EXISTS pimee_data_quality_insights_dictionary_depr;
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
