<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20190404122452 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP INDEX UNIQ_F19B3719A5D32530 ON akeneo_file_storage_file_info');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F19B3719A5D32530 ON akeneo_file_storage_file_info (file_key)');
    }
}
