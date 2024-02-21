<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Application;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model\IndexMigration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Query\IndexMigrationRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWithoutDowntime;
use Akeneo\Tool\Component\Elasticsearch\ClockInterface;
use Akeneo\Tool\Component\Elasticsearch\PublicApi\Write\MigrateIndexWithoutDowntime;
use Akeneo\Tool\Component\Elasticsearch\PublicApi\Write\MigrateIndexWithoutDowntimeHandlerInterface;

class MigrateIndexWithoutDowntimeHandler implements MigrateIndexWithoutDowntimeHandlerInterface
{
    private ClockInterface $clock;
    private UpdateIndexMappingWithoutDowntime $updateIndexMappingWithoutDowntime;
    private IndexMigrationRepositoryInterface $indexMigrationRepository;

    public function __construct(
        ClockInterface $clock,
        UpdateIndexMappingWithoutDowntime $updateIndexMappingWithoutDowntime,
        IndexMigrationRepositoryInterface $indexMigrationRepository
    ) {
        $this->clock = $clock;
        $this->updateIndexMappingWithoutDowntime = $updateIndexMappingWithoutDowntime;
        $this->indexMigrationRepository = $indexMigrationRepository;
    }

    public function handle(MigrateIndexWithoutDowntime $command)
    {
        $currentDatetime = $this->clock->now();
        $temporaryIndexAlias = $this->initTemporaryIndexAlias($command, $currentDatetime);
        $migratedIndexName = $this->initMigratedIndexName($temporaryIndexAlias);

        $indexMigration = IndexMigration::create(
            $command->getIndexAlias(),
            $command->getIndexConfiguration()->getHash(),
            $currentDatetime,
            $temporaryIndexAlias,
            $migratedIndexName
        );

        $this->updateIndexMappingWithoutDowntime->execute(
            $command->getIndexAlias(),
            $temporaryIndexAlias,
            $migratedIndexName,
            $command->getIndexConfiguration(),
            $command->getFindUpdatedDocumentQuery()
        );

        $indexMigration->markAsDone();
        $this->indexMigrationRepository->save($indexMigration);
    }

    private function initTemporaryIndexAlias(
        MigrateIndexWithoutDowntime $command,
        \DateTimeImmutable $currentDatetime
    ): string {
        return sprintf('%s_%s', $command->getIndexAlias(), $currentDatetime->getTimestamp());
    }

    private function initMigratedIndexName(string $temporaryIndexAlias): string
    {
        return sprintf('%s_index', $temporaryIndexAlias);
    }
}
