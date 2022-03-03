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
        $withStats = $input->getOption('with-stats');
        $context = new Context($input->getOption('dry-run'), $withStats);

        foreach ($this->steps as $step) {
            $this->contextProcessor->push(['step' => $step->getName()]);
            $this->logger->notice($step->getDescription());

            if ($withStats) {
                $missingCount = $step->getMissingCount();
                $this->contextProcessor->push(['total_missing_items_count' => $missingCount]);
                $this->logger->notice('Missing items');
            } else {
                $this->contextProcessor->push(['total_missing_items_count' => 0]); // TODO is 0 ok?
            }

            if ($step->shouldBeExecuted()) {
                $this->logger->notice('Add missing items');
                $stepStartTime = \microtime(true);
                if (!$step->addMissing($context, $output)) {
                    $this->logger->notice('An item can not be migrated. Step stopped.');

                    return Command::FAILURE;
                }
                $stepDuration = \microtime(true) - $stepStartTime;

                $this->logger->notice(\sprintf('Step done in %0.2f seconds', $stepDuration));
            } else {
                $this->logger->notice('No items to migrate. Step skipped.');
            }

            $this->contextProcessor->pop(); //pop total missing items count
            $this->contextProcessor->pop(); //pop step name
        }

        $this->logger->notice('Migration done!');

        return Command::SUCCESS;
    }
}
