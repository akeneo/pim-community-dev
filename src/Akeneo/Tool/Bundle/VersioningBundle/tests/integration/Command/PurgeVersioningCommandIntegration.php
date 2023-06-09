<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Command;

use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
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
    public function it_does_not_launch_purge_if_lock_exists(): void
    {
        /** @var LockFactory $lockFactory */
        $lockFactory = $this->get('pim_framework.lock.factory');
        $lockIdentifier = 'scheduled-job-versioning_purge';
        $lock = $lockFactory->createLock($lockIdentifier, 300, false);
        $lock->acquire();

        $output = $this->runPurgeCommand();
        $result = $output->fetch();

        $lock->release();

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
    public function it_does_launch_purge_if_lock_expires(): void
    {
        $expectedOriginalVersionsCount = 25;
        $this->initializeVersions($expectedOriginalVersionsCount);

        $expectedDeletedVersionsCount = 18;

        /** @var LockFactory $lockFactory */
        $lockFactory = $this->get('pim_framework.lock.factory');
        $lockIdentifier = 'scheduled-job-versioning_purge';
        $lock = $lockFactory->createLock($lockIdentifier, 2, false);
        $lock->acquire();

        sleep(3);

        $output = $this->runPurgeCommand();
        $result = $output->fetch();

        $this->assertPurgeResult($result, $expectedOriginalVersionsCount, $expectedDeletedVersionsCount, 0);
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
