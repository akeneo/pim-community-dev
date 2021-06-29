<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_5_0_20201117133700_add_dqi_unique_score_tables extends AbstractMigration
{

    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
CREATE TABLE pim_data_quality_insights_product_score (
    product_id INT NOT NULL,
    evaluated_at DATE NOT NULL,
    scores JSON NOT NULL,
    PRIMARY KEY (product_id, evaluated_at),
    INDEX evaluated_at_index (evaluated_at),
    CONSTRAINT FK_dqi_product_score FOREIGN KEY (product_id) REFERENCES pim_catalog_product (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        $this->addSql(<<<SQL
CREATE TABLE pim_data_quality_insights_dashboard_scores_projection (
    type VARCHAR(15) NOT NULL,
    code VARCHAR(100) NOT NULL,
    scores JSON NOT NULL,
    PRIMARY KEY (type, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

}
