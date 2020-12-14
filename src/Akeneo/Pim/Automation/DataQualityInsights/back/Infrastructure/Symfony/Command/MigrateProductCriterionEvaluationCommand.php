<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateProductCriterionEvaluationCommand extends Command
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        parent::__construct();

        $this->dbConnection = $dbConnection;
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:migrate-product-criterion-evaluation')
            ->setDescription('Migrate the products criteria evaluations with empty results and pending status.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->isMigrationDone()) {
            $output->writeln('The migration has already been performed.');

            return 0;
        }

        $output->writeln('Start migration of products...');

        $this->dbConnection->executeQuery(<<<SQL
INSERT IGNORE INTO pim_data_quality_insights_product_criteria_evaluation (product_id, criterion_code, evaluated_at, status)
SELECT evaluation_depr.product_id, evaluation_depr.criterion_code, evaluation_depr.evaluated_at, 'pending'
FROM pim_data_quality_insights_product_criteria_evaluation_depr AS evaluation_depr
WHERE evaluation_depr.criterion_code != 'consistency_text_title_formatting';

DROP TABLE pim_data_quality_insights_product_criteria_evaluation_depr;
SQL
        );

        $output->writeln('Start migration of product models...');

        $this->dbConnection->executeQuery(<<<SQL

INSERT IGNORE INTO pim_data_quality_insights_product_model_criteria_evaluation (product_id, criterion_code, evaluated_at, status)
SELECT evaluation_depr.product_id, evaluation_depr.criterion_code, evaluation_depr.evaluated_at, 'pending'
FROM pim_data_quality_insights_product_model_criteria_evaluation_depr AS evaluation_depr
WHERE evaluation_depr.criterion_code != 'consistency_text_title_formatting';

DROP TABLE pim_data_quality_insights_product_model_criteria_evaluation_depr;
SQL
    );


        $output->writeln('Migration done.');

        return 0;
    }

    private function isMigrationDone(): bool
    {
        $query = <<<SQL
SHOW TABLES LIKE 'pim_data_quality_insights_product_%_depr';
SQL;
        $tablesToMigrate = $this->dbConnection->executeQuery($query)->fetchAll();

        return empty($tablesToMigrate);
    }
}
