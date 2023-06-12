<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Command;

use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Lock\LockFactory;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeVersioningCommandIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_purges_versions_but_keeps_the_first_and_last_version_of_a_family(): void
    {
        $expectedOriginalVersionsCount = 25;
        $this->initializeVersions($expectedOriginalVersionsCount);

        $output = $this->runPurgeCommand(['--more-than-days' => 0]);
        $result = $output->fetch();

        $expectedDeletedVersionsCount = 18;

        $this->assertPurgeResult($result, $expectedOriginalVersionsCount, $expectedDeletedVersionsCount, 0);
    }

    /**
     * @test
     */
    public function it_does_not_launch_purge_if_another_one_is_running(): void
    {
        $expectedOriginalVersionsCount = 25;
        $this->initializeVersions($expectedOriginalVersionsCount);

        // run the first command
        $output = $this->runPurgeCommand(['--more-than-days' => 0]);

        $pattern = '/Step execution starting: id=(\d+), name=\[versioning_purge\]/';
        preg_match($pattern, $output->fetch(), $matches);
        $firstExecutionId = (int)$matches[1];
        var_dump($firstExecutionId);

        // Set this execution at status 'STARTED'
        $this->setExecutionStatus($firstExecutionId, new BatchStatus(BatchStatus::STARTED));

        $output = $this->runPurgeCommand(['--more-than-days' => 0]);
        $result = $output->fetch();

        Assert::assertStringContainsString(
            '[app] Cannot launch scheduled job because another execution is still running.',
            $result
        );
        Assert::assertStringNotContainsString(
            'Start purging versions',
            $result
        );
    }

    /**
     * @test
     */
    public function it_purges_versions_with_command_parameter(): void
    {
        $expectedOriginalVersionsCount = 25;
        $this->initializeVersions($expectedOriginalVersionsCount);

        $output = $this->runPurgeCommand(['--more-than-days' => 5]);
        $result = $output->fetch();

        $expectedDeletedVersionsCount = 10;

        $this->assertPurgeResult($result, $expectedOriginalVersionsCount, $expectedDeletedVersionsCount, 5);
    }

    /**
     * @test
     */
    public function it_purges_versions_with_default_parameter(): void
    {
        $expectedOriginalVersionsCount = 25;
        $this->initializeVersions($expectedOriginalVersionsCount);

        $output = $this->runPurgeCommand();
        $result = $output->fetch();

        $expectedDeletedVersionsCount = 0;

        $this->assertPurgeResult($result, $expectedOriginalVersionsCount, $expectedDeletedVersionsCount, 90);
    }

    private function initializeVersions(int $expectedOriginalVersionsCount): void
    {
        $limitDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->givenFamilyVersionsOlderThan($limitDate, 8, 35);
        $this->givenFamilyVersionsOlderThan($limitDate, 12, 44);
        $this->givenFamilyVersionsAtLeastAsYoungAs($limitDate, 2, 35, 9);
        $this->givenFamilyVersionsAtLeastAsYoungAs($limitDate, 3, 44, 13);

        Assert::assertEquals($expectedOriginalVersionsCount, $this->countVersions());
    }

    private function countVersions(): int
    {
        return (int)$this->getConnection()
            ->executeQuery('SELECT count(*) FROM pim_versioning_version')
            ->fetchOne();
    }

    private function assertPurgeResult(
        string $commandOutput,
        int $expectedOriginalVersionsCount,
        int $expectedDeletedVersionsCount,
        int $purgeDaysNumber,
    ): void {
        Assert::assertStringContainsString(
            sprintf('You are about to process versions of %s older than %d days', Family::class, $purgeDaysNumber),
            $commandOutput
        );

        Assert::assertStringContainsString(
            sprintf('Start purging versions of %s (1/1)', Family::class),
            $commandOutput
        );
        Assert::assertStringContainsString(
            sprintf('Versions count = %d', $expectedOriginalVersionsCount),
            $commandOutput
        );
        if ($expectedDeletedVersionsCount > 0) {
            Assert::assertStringContainsString(
                sprintf('Successfully deleted %s versions', $expectedDeletedVersionsCount),
                $commandOutput
            );
        } else {
            Assert::assertStringContainsString(
                'There are no versions to purge.',
                $commandOutput
            );
        }

        $expectedRemainingVersionsCount = $expectedOriginalVersionsCount - $expectedDeletedVersionsCount;
        $versionsCount = $this->countVersions();
        Assert::assertEquals($expectedRemainingVersionsCount, $versionsCount);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->executeQuery('DELETE FROM pim_versioning_version');
    }

    private function givenFamilyVersionsAtLeastAsYoungAs(
        \DateTime $limitDate,
        int $count,
        int $resourceId,
        int $startingVersion
    ): void {
        $loggedAt = clone $limitDate;
        for ($i = 0; $i < $count; $i++) {
            $this->createVersion(Family::class, $resourceId, $loggedAt, $startingVersion);
            $loggedAt->modify('+1 DAY');
            $startingVersion++;
        }
    }

    private function givenFamilyVersionsOlderThan(\DateTime $limitDate, int $count, int $resourceId): void
    {
        for ($i = $count; $i > 0; $i--) {
            $loggedAt = clone $limitDate;
            $loggedAt->modify(sprintf('-%d DAY', $i));
            $this->createVersion(Family::class, $resourceId, $loggedAt, $i);
        }
    }

    private function createVersion(
        string $resourceName,
        int $resourceId,
        \DateTime $loggedAt,
        int $versionNumber = 1
    ): int {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        $version = new Version($resourceName, $resourceId, null, 'system');
        $version->setVersion($versionNumber);
        $entityManager->persist($version);
        $entityManager->flush();

        $this->get('database_connection')->executeQuery(
            'UPDATE pim_versioning_version SET logged_at = :logged_at WHERE id = :version_id',
            [
                'logged_at' => $loggedAt->format('Y-m-d H:i:s'),
                'version_id' => $version->getId(),
            ],
            [
                'logged_at' => \PDO::PARAM_STR,
                'version_id' => \PDO::PARAM_INT,
            ]
        );

        return $version->getId();
    }

    private function setExecutionStatus($stepExecutionId, BatchStatus $status): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $jobExecutionId = $connection->executeQuery(
            sprintf('SELECT job_execution_id FROM akeneo_batch_step_execution WHERE id=%d', $stepExecutionId)
        )->fetchOne();

        /** @var JobExecutionRepository $repository */
        $repository = $this->get('pim_enrich.repository.job_execution');
        /** @var JobExecution $jobExecution */
        $jobExecution = $repository->find($jobExecutionId);

        $jobExecution->setStatus($status);
        $jobExecution->setHealthcheckTime(new \DateTime());

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($jobExecution);
        $em->flush();
    }

    /**
     * Launch the purge command in verbose mode to test output
     */
    private function runPurgeCommand(array $arrayInput = []): BufferedOutput
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $defaultArrayInput = [
            'command' => 'pim:versioning:purge',
            'entity' => Family::class,
            '--more-than-days' => null,
            '--force' => null,
            '-vv',
        ];

        $arrayInput = array_merge($defaultArrayInput, $arrayInput);
        if (isset($arrayInput['--config'])) {
            $arrayInput['--config'] = json_encode($arrayInput['--config']);
        }

        $input = new ArrayInput($arrayInput);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }
}
