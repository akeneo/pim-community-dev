<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidCommand extends Command
{
    protected static $defaultName = 'pim:product:migrate-to-uuid';

    public function __construct(
        private MigrateToUuidCreateColumns $migrateToUuidCreateColumns,
        private MigrateToUuidFillUuids $migrateToUuidFillUuids,
        private MigrateToUuidFillJson $migrateToUuidFillJson,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Migrate databases to product uuids');
        $this->addOption('dry-run', 'd', InputOption::VALUE_NEGATABLE, 'dry run', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->getOption('dry-run');

        foreach ([$this->migrateToUuidCreateColumns, $this->migrateToUuidFillUuids, $this->migrateToUuidFillJson] as $step) {
            $missingCount = $step->getMissingCount($output);
            $output->writeln(sprintf('Missing %d elements', $missingCount));
            if ($missingCount > 0 && !$dryRun) {
                $output->writeln(sprintf('Add missing elements...'));
                $step->addMissing($output);
                $output->writeln(sprintf('Done'));
            }

            if ($missingCount > 0 && $dryRun) {
                $output->writeln('Enleve le dry run avant de continuer michel');
                return Command::SUCCESS;
            }
        }

        $output->writeln(sprintf('LEZGO'));

        return Command::SUCCESS;
    }
}
