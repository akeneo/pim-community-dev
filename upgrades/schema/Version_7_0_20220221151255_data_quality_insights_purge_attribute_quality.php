<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220221151255_data_quality_insights_purge_attribute_quality extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Purge table pimee_dqi_attribute_locale_quality and pimee_dqi_attribute_quality and add foreign key on this same tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        DELETE dqi FROM pimee_dqi_attribute_locale_quality as dqi
        LEFT JOIN pim_catalog_attribute a ON a.code = dqi.attribute_code
        WHERE a.id IS NULL;
        SQL
        );

        $this->addSql(<<<SQL
        DELETE dqi FROM pimee_dqi_attribute_quality as dqi
        LEFT JOIN pim_catalog_attribute a ON a.code = dqi.attribute_code
        WHERE a.id IS NULL;
        SQL
        );

        $this->addSql(<<<SQL
        ALTER TABLE pimee_dqi_attribute_locale_quality
        ADD CONSTRAINT FK_pimeedqi_attribute_locale_quality_as_code
        FOREIGN KEY (attribute_code)
        REFERENCES pim_catalog_attribute(code)
        ON DELETE CASCADE;
        SQL
        );

        $this->addSql(<<<SQL
        ALTER TABLE pimee_dqi_attribute_quality
        ADD CONSTRAINT FK_pimeedqi_attribute_quality_as_code
        FOREIGN KEY (attribute_code)
        REFERENCES pim_catalog_attribute (code)
        ON DELETE CASCADE
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
