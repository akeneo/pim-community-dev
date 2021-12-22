<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211222080930_trim_locales_codes extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE pim_catalog_locale SET code = TRIM(code)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
