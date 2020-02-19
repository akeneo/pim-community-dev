<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Command;

use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

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
        $limitDate = new \DateTime('now');

        $this->givenFamilyVersionsOlderThan($limitDate, 8, 35);
        $this->givenFamilyVersionsOlderThan($limitDate, 12, 44);
        $this->givenFamilyVersionsAtLeastAsYoungAs($limitDate, 2, 35, 9);
        $this->givenFamilyVersionsAtLeastAsYoungAs($limitDate, 3, 44, 13);

        $versionsCount = $this->getConnection()->executeQuery('SELECT count(*) FROM pim_versioning_version')->fetchColumn();
        Assert::assertEquals(25, $versionsCount);

        $output = $this->runPurgeCommand();
        $result = $output->fetch();

        Assert::assertStringContainsString(sprintf('Start purging versions of %s (1/1)', Family::class), $result);
        Assert::assertStringContainsString('Successfully deleted 18 versions', $result);

        $versionsCount = $this->getConnection()->executeQuery('SELECT count(*) FROM pim_versioning_version')->fetchColumn();
        Assert::assertEquals(7, $versionsCount);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration()
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

    private function givenFamilyVersionsAtLeastAsYoungAs(\DateTime $limitDate, int $count, int $resourceId, int $startingVersion): array
    {
        $loggedAt = clone $limitDate;
        $versionIds = [];
        for ($i = 0; $i < $count; $i++) {
            $versionIds[] = $this->createVersion(Family::class, $resourceId, $loggedAt, $startingVersion);
            $loggedAt->modify('+1 DAY');
            $startingVersion++;
        }

        return $versionIds;
    }

    private function givenFamilyVersionsOlderThan(\DateTime $limitDate, int $count, int $resourceId): array
    {
        $versionIds = [];
        for ($i = $count; $i > 0; $i--) {
            $loggedAt = clone $limitDate;
            $loggedAt->modify(sprintf('-%d DAY', $i));
            $versionIds[] = $this->createVersion(Family::class, $resourceId, $loggedAt, $i);
        }

        return $versionIds;
    }

    private function createVersion(string $resourceName, int $resourceId, \DateTime $loggedAt, int $versionNumber = 1): int
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        $version = new Version($resourceName, $resourceId, 'system');
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

    private function runPurgeCommand(array $arrayInput = []): BufferedOutput
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $defaultArrayInput = [
            'command' => 'pim:versioning:purge',
            'entity' => Family::class,
            '--more-than-days' => 0,
            '--force' => null,
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
