<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

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
        private LoggerInterface $logger,
        MigrateToUuidStep $migrateToUuidCreateColumns,
        MigrateToUuidStep $migrateToUuidFillProductUuid,
        MigrateToUuidStep $migrateToUuidFillForeignUuid,
        MigrateToUuidStep $migrateToUuidFillJson
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

        foreach ($this->steps as $stepIndex => $step) {
            $this->logger->info(sprintf('<info>Step %d: %s</info>', $stepIndex + 1, $step->getDescription()));
            if ($withStats) {
                $missingCount = $step->getMissingCount();
                $this->logger->info(sprintf('    Missing %d items', $missingCount));
            }

            if ($step->shouldBeExecuted()) {
                $this->logger->info('    Add missing items... ');
                $step->addMissing($dryRun);
                $this->logger->info('    Step done');
            } else {
                $this->logger->info('    No items to migrate, skip.');
            }
        }

        $this->logger->info('<info>Migration done!</info>');

        return Command::SUCCESS;
    }
}
