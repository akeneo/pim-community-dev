<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220314172032_dqi_add_foreign_keys_on_pimee_dqi_attribute_option_spellcheck extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign keys on pimee_dqi_attribute_option_spellcheck';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE pimee_dqi_attribute_option_spellcheck
            ADD CONSTRAINT FK_dqi_attribute_code 
                FOREIGN KEY (attribute_code) REFERENCES pim_catalog_attribute (code) ON DELETE CASCADE;

            ALTER TABLE pimee_dqi_attribute_option_spellcheck
            ADD CONSTRAINT FK_dqi_attribute_option_code 
                FOREIGN KEY (attribute_option_code) REFERENCES pim_catalog_attribute_option (code) ON DELETE CASCADE;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
