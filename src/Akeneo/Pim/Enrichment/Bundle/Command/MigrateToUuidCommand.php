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
        private MigrateToUuidCreateColumns $migrateToUuidCreateColumns
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

        $missingCount = $this->migrateToUuidCreateColumns->getMissingCount($output);
        $output->writeln(sprintf('Step1: Missing %d columns', $missingCount));
        if ($missingCount > 0 && !$dryRun) {
            $output->writeln(sprintf('Add missing columns...'));
            $this->migrateToUuidCreateColumns->addMissing($output);
            $output->writeln(sprintf('Done'));
        }

        $output->writeln(sprintf('LEZGO'));

        return Command::SUCCESS;
    }
}
