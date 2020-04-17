<?php

declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class Version_5_0_20200416124645_data_quality_insights_rework_evaluation_tables_Integration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_reworks_criteria_evaluations_tables()
    {
        $productEvaluationsKept = [
            [
                'id' => strval(Uuid::uuid4()),
                'product_id' => 42,
                'criterion_code' => 'completeness',
                'created_at' => '2020-04-16 12:34:53',
                'started_at' => null,
                'ended_at' => null,
                'status' => 'pending',
                'result' => '{"data": {"test": "product 42 completeness last entry"}}',
                'pending' => 1
            ],
            [
                'id' => strval(Uuid::uuid4()),
                'product_id' => 42,
                'criterion_code' => 'spelling',
                'created_at' => '2020-04-16 12:34:37',
                'started_at' => null,
                'ended_at' => null,
                'status' => 'pending',
                'result' => '{"data": {"test": "product 42 spelling last entry"}}',
                'pending' => 1
            ],
            [
                'id' => strval(Uuid::uuid4()),
                'product_id' => 123,
                'criterion_code' => 'spelling',
                'created_at' => '2020-04-16 16:24:28',
                'started_at' => null,
                'ended_at' => null,
                'status' => 'pending',
                'result' => '{"data": {"test": "product 123 spelling last entry"}}',
                'pending' => 1
            ],
        ];

        $productEvaluationsRemoved = [
            [
                'id' => strval(Uuid::uuid4()),
                'product_id' => 42,
                'criterion_code' => 'completeness',
                'created_at' => '2020-04-13 11:34:27',
                'started_at' => '2020-04-13 11:34:28',
                'ended_at' => '2020-04-13 11:34:29',
                'status' => 'done',
                'result' => '{"data": {"test": "product 42 completeness deprecated result"}}',
                'pending' => null
            ],
            [
                'id' => strval(Uuid::uuid4()),
                'product_id' => 42,
                'criterion_code' => 'completeness',
                'created_at' => '2020-04-11 11:34:27',
                'started_at' => '2020-04-11 11:34:28',
                'ended_at' => '2020-04-11 11:34:29',
                'status' => 'done',
                'result' => '{"data": {"test": "product 42 completeness other deprecated result"}}',
                'pending' => null
            ],
        ];

        $productModelEvaluationsKept = [
            [
                'id' => strval(Uuid::uuid4()),
                'product_id' => 71,
                'criterion_code' => 'spelling',
                'created_at' => '2020-04-16 12:34:37',
                'started_at' => null,
                'ended_at' => null,
                'status' => 'pending',
                'result' => '{"data": {"test": "product model 71 spelling last entry"}}',
                'pending' => 1
            ],
        ];

        $productModelsEvaluationsRemoved = [
            [
                'id' => strval(Uuid::uuid4()),
                'product_id' => 71,
                'criterion_code' => 'spelling',
                'created_at' => '2020-04-11 11:34:27',
                'started_at' => '2020-04-11 11:34:28',
                'ended_at' => '2020-04-11 11:34:29',
                'status' => 'done',
                'result' => '{"data": {"test": "product 71 spelling deprecated result"}}',
                'pending' => null
            ],
        ];

        $this->persistProductEvaluations($productEvaluationsRemoved);
        $this->persistProductEvaluations($productEvaluationsKept);

        $this->persistProductModelEvaluations($productModelEvaluationsKept);
        $this->persistProductModelEvaluations($productModelsEvaluationsRemoved);

        $this->runMigration();

        $this->assertProductEvaluationsCount(count($productEvaluationsKept));
        $this->assertProductEvaluationsExist($productEvaluationsKept);

        $this->assertProductModelEvaluationsCount(count($productModelEvaluationsKept));
        $this->assertProductModelEvaluationsExist($productModelEvaluationsKept);
    }

    private function persistProductEvaluations($evaluations): void
    {
        $this->persistEvaluations($evaluations, 'pimee_data_quality_insights_criteria_evaluation');
    }

    private function persistProductModelEvaluations($evaluations): void
    {
        $this->persistEvaluations($evaluations, 'pimee_data_quality_insights_product_model_criteria_evaluation');
    }

    private function persistEvaluations($evaluations, string $evaluationsTable): void
    {
        $dbConnection = $this->get('database_connection');
        $query = <<<SQL
INSERT INTO $evaluationsTable
    (id, criterion_code, product_id, created_at, started_at, ended_at, status, result, pending) 
    VALUES (:id, :criterion_code, :product_id, :created_at, :started_at, :ended_at, :status, :result, :pending)
SQL;

        foreach ($evaluations as $evaluation) {
            $dbConnection->executeQuery($query, $evaluation);
        }
    }

    private function runMigration(): void
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $migration);
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function assertProductEvaluationsCount(int $expectedCount): void
    {
        $this->assertEvaluationsCount($expectedCount, 'pimee_data_quality_insights_product_criteria_evaluation');
    }

    private function assertProductModelEvaluationsCount(int $expectedCount): void
    {
        $this->assertEvaluationsCount($expectedCount, 'pimee_data_quality_insights_product_model_criteria_evaluation');
    }

    private function assertEvaluationsCount(int $expectedCount, string $evaluationsTable):void
    {
        $stmt = $this->get('database_connection')->executeQuery(
            "SELECT COUNT(*) FROM $evaluationsTable"
        );

        $this->assertSame($expectedCount, intval($stmt->fetchColumn()));
    }

    private function assertProductEvaluationsExist(array $evaluations): void
    {
        $this->assertEvaluationsExist($evaluations, 'pimee_data_quality_insights_product_criteria_evaluation');
    }

    private function assertProductModelEvaluationsExist(array $evaluations): void
    {
        $this->assertEvaluationsExist($evaluations, 'pimee_data_quality_insights_product_model_criteria_evaluation');
    }

    private function assertEvaluationsExist(array $evaluations, string $evaluationsTable): void
    {
        $dbConnection = $this->get('database_connection');
        $query = <<<SQL
SELECT 1 FROM $evaluationsTable
WHERE product_id = :product_id AND criterion_code = :criterion_code AND evaluated_at = :evaluated_at AND status = :status
SQL;

        foreach ($evaluations as $evaluation) {
            $stmt = $dbConnection->executeQuery($query, [
                'product_id' => $evaluation['product_id'],
                'criterion_code' => $evaluation['criterion_code'],
                'evaluated_at' => $evaluation['created_at'],
                'status' => $evaluation['status'],
            ]);

            $this->assertTrue((bool) $stmt->fetchColumn(), sprintf(
                'The evaluation of the criterion %s for the product %d should exists', $evaluation['criterion_code'], $evaluation['product_id']
            ));
        }
    }
}
