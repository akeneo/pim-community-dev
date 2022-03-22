<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\LogContext;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ElasticsearchProjection\GetElasticsearchProductProjection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidCommand extends Command
{
    protected static $defaultName = 'pim:product:migrate-to-uuid';
    /** @var array<MigrateToUuidStep> */
    private array $steps;

    public function __construct(
        MigrateToUuidStep $migrateToUuidCreateIndexes,
        MigrateToUuidStep $migrateToUuidAddTriggers,
        MigrateToUuidStep $migrateToUuidFillProductUuid,
        MigrateToUuidStep $migrateToUuidFillForeignUuid,
        MigrateToUuidStep $migrateToUuidFillJson,
        MigrateToUuidStep $migrateToUuidSetNotNullableUuidColumns,
        MigrateToUuidStep $migrateToUuidReindexElasticsearch,
        private LoggerInterface $logger
    ) {
        parent::__construct();
        $this->steps = [
            $migrateToUuidCreateIndexes,
            $migrateToUuidAddTriggers,
            $migrateToUuidFillProductUuid,
            $migrateToUuidFillForeignUuid,
            $migrateToUuidFillJson,
            $migrateToUuidSetNotNullableUuidColumns,
            $migrateToUuidReindexElasticsearch,
        ];
    }

    protected function configure()
    {
        $this->setDescription('Migrate databases to product uuids');
        $this->addOption('dry-run', 'd', InputOption::VALUE_NEGATABLE, 'dry run', false);
        $this->addOption('with-stats', 's', InputOption::VALUE_NEGATABLE, 'Display stats (be careful the command is way too slow)', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $withStats = $input->getOption('with-stats');
        $context = new Context($input->getOption('dry-run'), $withStats);

        $startMigrationTime = \time();
        $this->logger->notice('Migration start');

        foreach ($this->steps as $step) {
            $logContext = new LogContext($step);
            $context->logContext = $logContext;

            if ($withStats) {
                $missingCount = $step->getMissingCount();
                $logContext->addContext('total_missing_items_count', $missingCount);
                $this->logger->notice('Missing items', $logContext->toArray());
            } else {
                $logContext->addContext('total_missing_items_count', null);
            }

            if ($step->shouldBeExecuted()) {
                $step->setStatusInProgress();
                $this->logger->notice('Start add missing items', $logContext->toArray());
                if (!$step->addMissing($context)) {
                    $step->setStatusInError();
                    $this->logger->error('An item can not be migrated. Step stopped.', $logContext->toArray());
                    $this->logger->notice('Migration stopped', ['migration_duration_in_second' => time() - $startMigrationTime]);
                    return Command::FAILURE;
                }
                $step->setStatusDone();
                $this->logger->notice(
                    \sprintf('Step done in %0.2f seconds', $step->getDuration()),
                    $logContext->toArray(['migration_duration_in_second' => time() - $startMigrationTime])
                );
            } else {
                $step->setStatusSkipped();
                $this->logger->notice(
                    'No items to migrate. Step skipped.',
                    $logContext->toArray(['migration_duration_in_second' => time() - $startMigrationTime])
                );
            }
        }

        $this->logger->notice('Migration done!', ['migration_duration_in_second' => time() - $startMigrationTime]);

        return Command::SUCCESS;
    }
}
