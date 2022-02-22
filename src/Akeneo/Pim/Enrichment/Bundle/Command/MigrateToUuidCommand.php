<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidCommand extends Command
{
    protected static $defaultName = 'pim:product:migrate-to-uuid';
    /** @var array<MigrateToUuidStep> */
    private array $steps;

    public function __construct(
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
            $output->writeln(sprintf('<info>Step %d: %s</info>', $stepIndex + 1, $step->getDescription()));
            if ($withStats) {
                $missingCount = $step->getMissingCount();
                $output->writeln(sprintf('    Missing %d items', $missingCount));
            }

            if ($step->shouldBeExecuted()) {
                $output->writeln(sprintf('    Add missing items... '));
                $step->addMissing($dryRun, $output);
                $output->writeln(sprintf('    Step done'));
            }
            $output->writeln('');
        }

        $output->writeln('<info>Migration done!</info>');

        return Command::SUCCESS;
    }
}
