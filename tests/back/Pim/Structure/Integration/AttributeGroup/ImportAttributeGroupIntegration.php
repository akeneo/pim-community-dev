<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ImportAttributeGroupIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_footwear_attribute_group_import';

    private JobLauncher $jobLauncher;
    private AttributeGroupRepositoryInterface $attributeGroupRepository;
    private JobExecutionRepository $jobExecutionRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->attributeGroupRepository = $this->get('pim_catalog.repository.attribute_group');
        $this->jobExecutionRepository = $this->get('pim_enrich.repository.job_execution');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_did_not_allow_to_create_an_attribute_when_limit_is_reached(): void
    {
        $this->createAttributeGroupsUntilLimit();
        $content = <<<CSV
        code;sort_order;label-en_US
        a_new_attribute_group;1;A new attribute group
        CSV;

        $this->launchImport($content);

        $this->assertAttributeGroupDoesNotExist('a_new_attribute_group');
        $this->assertJobIsSuccessWithWarning("You’ve reached the limit of 1000 attribute groups: A new attribute group\n");
    }

    public function test_it_allow_to_update_an_attribute_when_limit_is_reached(): void
    {
        $this->createAttributeGroupsUntilLimit();
        $this->assertAttributeGroupExist('marketing', '[marketing]', 2);
        $this->assertAttributeGroupExist('info', '[info]', 1);

        $content = <<<CSV
        code;sort_order;label-en_US
        marketing;1;Marketing
        info;2;Product information
        CSV;
        $this->launchImport($content);

        $this->assertAttributeGroupExist('marketing', '[marketing]', 1);
        $this->assertAttributeGroupExist('info', '[info]', 2);
    }

    private function getAttributeGroup(string $attributeGroupCode): ?AttributeGroup
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();

        return $this->attributeGroupRepository->findOneByIdentifier($attributeGroupCode);
    }

    private function launchImport(string $content): void
    {
        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $content);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }

    private function assertJobIsSuccessWithWarning(string $expectedWarningReason): void
    {
        $jobExecution = $this->getLastJobExecution(self::CSV_IMPORT_JOB_CODE);

        $this->assertSame(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        $warningReasons = [];
        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            foreach ($stepExecution->getWarnings() as $warning) {
                $warningReasons[] = $warning->getReason();
            }
        }

        $this->assertContains(
            $expectedWarningReason,
            $warningReasons,
            sprintf("Warning $expectedWarningReason was not found in %s", implode($warningReasons))
        );
    }

    private function getLastJobExecution(string $jobCode): JobExecution
    {
        $sql = <<< EOS
            SELECT e.id 
            FROM akeneo_batch_job_execution e 
            INNER JOIN akeneo_batch_job_instance j ON e.job_instance_id = j.id 
            WHERE j.code = :job_code
            LIMIT 1;
        EOS;

        $lastJobExecutionId = $this->connection->executeQuery($sql, ['job_code' => $jobCode])->fetchOne();

        return $this->jobExecutionRepository->findOneById($lastJobExecutionId);
    }

    private function assertAttributeGroupExist(string $code, string $label, int $sortOrder): void
    {
        $attributeGroup = $this->getAttributeGroup($code);

        self::assertNotNull($attributeGroup);
        self::assertSame($code, $attributeGroup->getCode());
        self::assertSame($label, $attributeGroup->getLabel());
        self::assertSame($sortOrder, $attributeGroup->getSortOrder());
    }

    private function assertAttributeGroupDoesNotExist(string $code): void
    {
        $attributeGroup = $this->getAttributeGroup($code);

        self::assertNull($attributeGroup);
    }

    private function createAttributeGroupsUntilLimit(): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $maxAttributeGroups = $this->getParameter('max_attribute_groups');

        $attributeGroupCount = (int) $connection->executeQuery('SELECT COUNT(*) FROM pim_catalog_attribute_group')->fetchOne();
        if ($attributeGroupCount >= $maxAttributeGroups) {
            return;
        }

        $maxSortOrder = (int) $connection->executeQuery('SELECT MAX(sort_order) FROM pim_catalog_attribute_group')->fetchOne();
        $sqlValues = [];
        for ($i = $attributeGroupCount; $i < $maxAttributeGroups; $i++) {
            $sqlValues[] = sprintf("('attribute_group_%d', %d, NOW(), NOW())", $i, $maxSortOrder + $i);
        }

        $sql = <<<SQL
INSERT INTO pim_catalog_attribute_group (code, sort_order, created, updated) VALUES %s
SQL;

        $connection->executeQuery(sprintf($sql, implode(', ', $sqlValues)));
    }
}
