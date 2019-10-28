<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\Repository\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Batch\Model\StepExecution;

class GetLastOperationsIntegration extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    public function testGetLastOperationsInTermsOfUser(): void
    {
        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $this->jobLauncher->launchExport('csv_product_export', 'admin');
        $this->jobLauncher->launchExport('csv_product_export', 'mary');
        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $julia = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');

        $lastOperations = $this->getLastOperationsQuery()->execute($julia);
        $this->assertCount(2, $lastOperations);

        foreach ($lastOperations as $lastOperation) {
            $this->assertCSVProductExportOperation($lastOperation);
        }
    }

    public function testOnlyGetNotBlackListedLastOperations(): void
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA2');
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'attribute_requirements' => [
                    'ecommerce' => ['a_date']
                ]
            ]
        );
        $this->get('pim_catalog.saver.family')->save($family);
        while ($this->jobLauncher->hasJobInQueue()) {
            $this->jobLauncher->launchConsumerOnce();
        }

        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $this->jobLauncher->launchExport('csv_product_export', 'admin');
        /**
         * Simulates the fact that julia updated the family via the UI
         * Else the job 'compute_completeness_of_products_family' (which is in black list) is a system job.
         */
        $this->changeUsernameJobs('system', 'julia');
        $julia = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');

        $lastOperations = $this->getLastOperationsQuery()->execute($julia);

        $this->assertCount(1, $lastOperations);

        foreach ($lastOperations as $lastOperation) {
            $this->assertCSVProductExportOperation($lastOperation);
        }
    }

    public function testGetLastOperationsWithGoodWarningCount(): void
    {
        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $this->addWarningsForJobs();

        $julia = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');

        $lastOperations = $this->getLastOperationsQuery()->execute($julia);

        $this->assertCount(2, $lastOperations);

        foreach ($lastOperations as $lastOperation) {
            $this->assertCSVProductExportOperation($lastOperation, '3');
        }
    }

    private function addWarningsForJobs(): void
    {
        $jobExecutions = $this->get('pim_enrich.repository.job_execution')->findBy(['user' => 'julia']);
        foreach ($jobExecutions as $jobExecution) {
            $stepExecution = $jobExecution->getStepExecutions()->first();
            $this->addWarningToStep($stepExecution, 3);
        }
    }

    private function assertCSVProductExportOperation(array $lastOperation, string $warningCount = '0'): void
    {
        $this->assertCount(7, $lastOperation);

        $expectedKeys = ['id', 'date', 'job_instance_id', 'type', 'label', 'status', 'warningCount'];
        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $lastOperation);
        }

        $this->assertNotEmpty($lastOperation['id']);
        $this->assertNotEmpty($lastOperation['job_instance_id']);
        $this->assertNotEmpty($lastOperation['date']);
        $this->assertEquals('export', $lastOperation['type']);
        $this->assertEquals('CSV product export', $lastOperation['label']);
        $this->assertEquals(1, $lastOperation['status']);
        $this->assertEquals($warningCount, $lastOperation['warningCount']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getLastOperationsQuery(): GetLastOperationsInterface
    {
        return $this->get('pim_import_export.query.get_last_operations');
    }

    private function changeUsernameJobs(string $from, string $to): void
    {
        $sql = <<<SQL
UPDATE akeneo_batch_job_execution
SET user = :to
WHERE user = :from;
SQL;

        $this->get('database_connection')->executeQuery($sql, [
            'to' => $to,
            'from' => $from,
        ]);
    }

    private function addWarningToStep(StepExecution $step, $number): void
    {
        $connection = $this->get('database_connection');
        while (0 !== $number) {
            $sql = <<<SQL
INSERT INTO akeneo_batch_warning (step_execution_id, reason, reason_parameters, item)
VALUES (:step_id, 'a reason', 'parameters', 'items');
SQL;
            $connection->executeQuery($sql, ['step_id' => $step->getId()]);
            $number--;
        }
    }
}
