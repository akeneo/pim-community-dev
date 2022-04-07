<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version_7_0_20220404152647_dqi_purge_product_score_and_modify_pk extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE old_scores
FROM pim_data_quality_insights_product_score AS old_scores
INNER JOIN pim_data_quality_insights_product_score AS younger_scores
    ON younger_scores.product_id = old_scores.product_id
    AND younger_scores.evaluated_at > old_scores.evaluated_at;');

        $this->addSql('ALTER TABLE pim_data_quality_insights_product_score DROP PRIMARY KEY, ADD PRIMARY KEY (product_id), ALGORITHM=INPLACE, LOCK=NONE;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
