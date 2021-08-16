<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Component\Elasticsearch\PublicApi\Read\IndexMigrationIsDoneInterface;
use Akeneo\Tool\Component\Elasticsearch\PublicApi\Write\MigrateIndexWithoutDowntime;
use Akeneo\Tool\Component\Elasticsearch\PublicApi\Write\MigrateIndexWithoutDowntimeHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command indexes all records on temporary index then change the alias used by the application to the temporary index.
 * This command is designed to be executed by a cron in SAAS environment to avoid having downtime when the index mapping changed.
 * Some index mapping update can be performed without having to reindex all records (on fly by elasticsearch), this command will reindexing anyway all records.
 *
 * This command cannot be launched in parallel because concurrent cron jobs are forbidden by configuration.
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class MigrateRecordsIndexMappingCommand extends Command
{
    public const CONFIGURATION_CODE = 'records_index_mapping_migration_%s';
    protected static $defaultName = 'akeneo:reference-entity:migrate-records-index-mapping';

    private IndexMigrationIsDoneInterface $indexMigrationIsDone;
    private MigrateIndexWithoutDowntimeHandlerInterface $migrateIndexWithoutDowntimeHandler;
    private Loader $configurationLoader;
    private string $recordIndexAlias;

    public function __construct(
        IndexMigrationIsDoneInterface $indexMigrationIsDone,
        MigrateIndexWithoutDowntimeHandlerInterface $migrateIndexWithoutDowntimeHandler,
        Loader $configurationLoader,
        string $recordIndexAlias
    ) {
        parent::__construct(self::$defaultName);

        $this->indexMigrationIsDone = $indexMigrationIsDone;
        $this->migrateIndexWithoutDowntimeHandler = $migrateIndexWithoutDowntimeHandler;
        $this->configurationLoader = $configurationLoader;
        $this->recordIndexAlias = $recordIndexAlias;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $currentIndexConfiguration = $this->configurationLoader->load();
        if ($this->currentMappingIsUpToDate($currentIndexConfiguration)) {
            $output->writeln('<info>The index mapping is up to date. Nothing to do.</info>');

            return 0;
        }

        if (!$io->confirm('Index mapping migration is needed, are you sure to continue?', true)) {
            $output->writeln('<info>Index mapping migration is cancelled</info>');

            return 0;
        }

        $indexMigrationCommand = new MigrateIndexWithoutDowntime(
            $this->recordIndexAlias,
            $currentIndexConfiguration,
            static fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ],
            ],
        );

        $this->migrateIndexWithoutDowntimeHandler->handle($indexMigrationCommand);

        $output->writeln('<info>Done</info>');

        return 0;
    }

    private function currentMappingIsUpToDate(IndexConfiguration $currentIndexConfiguration): bool
    {
        return $this->indexMigrationIsDone->byIndexAliasAndHash(
            $this->recordIndexAlias,
            $currentIndexConfiguration->getHash()
        );
    }
}
