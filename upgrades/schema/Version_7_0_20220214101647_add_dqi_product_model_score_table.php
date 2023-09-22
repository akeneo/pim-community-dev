<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version_7_0_20220214101647_add_dqi_product_model_score_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
CREATE TABLE IF NOT EXISTS pim_data_quality_insights_product_model_score (
    product_model_id INT NOT NULL PRIMARY KEY,
    evaluated_at DATE NOT NULL,
    scores JSON NOT NULL,
    CONSTRAINT FK_dqi_product_model_score FOREIGN KEY (product_model_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
