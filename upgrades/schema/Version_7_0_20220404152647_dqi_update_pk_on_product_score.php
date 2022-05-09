<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version_7_0_20220404152647_dqi_update_pk_on_product_score extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pim_data_quality_insights_product_score DROP PRIMARY KEY, ADD PRIMARY KEY (product_id), ALGORITHM=INPLACE, LOCK=NONE;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
