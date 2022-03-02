<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StackedContextProcessor;
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
        MigrateToUuidStep $migrateToUuidCreateColumns,
        MigrateToUuidStep $migrateToUuidFillProductUuid,
        MigrateToUuidStep $migrateToUuidFillForeignUuid,
        MigrateToUuidStep $migrateToUuidFillJson,
        private LoggerInterface $logger,
        private StackedContextProcessor $contextProcessor
    ) {
        parent::__construct();
        $this->steps = [
            $migrateToUuidCreateColumns,
            $migrateToUuidFillProductUuid,
            $migrateToUuidFillForeignUuid,
            $migrateToUuidFillJson,
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
        $dryRun = $input->getOption('dry-run');
        $withStats = $input->getOption('with-stats');
        $itemNotMigrated = false;

        foreach ($this->steps as $stepIndex => $step) {
            if ($itemNotMigrated) {
                continue;
            }
            $this->contextProcessor->push(['step'=> $stepIndex + 1]);
            $this->logger->notice($step->getDescription());
            if ($withStats) {
                $missingCount = $step->getMissingCount();
                $this->logger->notice('Missing items', ['missing_item_counts'=>$missingCount]);
            }

            if ($step->shouldBeExecuted()) {
                // TODO Add the with-stats to not count anything in the next steps
                // TODO Add timing for each migration
                $this->logger->notice('Add missing items');
                $allItemsMigrated = $step->addMissing($dryRun);
                if ($allItemsMigrated) {
                    $this->logger->notice('Step done');
                } else {
                    $this->logger->notice('An item can not be migrated. Step stopped!');
                    $itemNotMigrated = true;
                }
            } else {
                $this->logger->notice('No items to migrate, Step skipped.');
            }
            $this->contextProcessor->pop();
        }

        $this->logger->notice('Migration done');

        return $itemNotMigrated ? Command::FAILURE : Command::SUCCESS;
    }
}
