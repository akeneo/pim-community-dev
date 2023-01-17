<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230116175236_update_product_identifier_column extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change the product identifier column to use a virtual generated column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT 1;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
