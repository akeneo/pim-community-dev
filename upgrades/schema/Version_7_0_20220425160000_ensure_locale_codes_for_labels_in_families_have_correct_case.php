<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version_7_0_20220425160000_ensure_locale_codes_for_labels_in_families_have_correct_case extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enforces all locale codes in the pim_catalog_family_translation to be correclty spelled regarding case (meaning same case as locale codes in table pim_catalog_locale)';
    }

    public function up(Schema $schema): void
    {
        // PIM-10416
        // for each family localized label search for the locale code into the locale table (case insensitive search)
        // for spelling if found, leave untouched if not found
        $this->addSql(
            <<<SQL
UPDATE pim_catalog_family_translation as TRANSLATION
    INNER JOIN (
        SELECT code 
        FROM pim_catalog_locale as p    
    ) as LOCALE
    ON TRANSLATION.locale = LOCALE.code
SET TRANSLATION.locale = COALESCE (LOCALE.code, TRANSLATION.locale);
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
