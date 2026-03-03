<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Cli\CheckCategoryTrees;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks whether categories tree are sane or corrupted
 *  - checks that (lft,right,lvl) is consistent with (parent_id,root_id) <- considered uncorrupted.
 *
 * @author    Weasels
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckCategoryTrees extends Command
{
    protected static $defaultName = 'akeneo:categories:check-order';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'dump-corruptions',
                'c',
                InputOption::VALUE_NONE,
                'Whether we dump detected corruptions or not',
            )
            ->addOption(
                'dump-fixed-order',
                't',
                InputOption::VALUE_NONE,
                'Whether we dump ordered categories or not',
            )
            ->addOption(
                'max-level',
                'm',
                InputArgument::OPTIONAL,
                'Max level for tree dumping',
                1,
            )
            ->addOption(
                'reorder',
                null,
                InputOption::VALUE_NONE,
                'Whether we update the categories in DB',
            )
            ->setDescription('Check all category trees against nested structure');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOptionDumpCorruptions = (bool) $input->getOption('dump-corruptions');
        $inputOptionDumpFixedOrder = (bool) $input->getOption('dump-fixed-order');
        $inputArgumentMaxLevel = $input->getOption('max-level');
        $inputOptionReorder = (bool) $input->getOption('reorder');

        if ($inputOptionReorder) {
            $output->writeln('Will update! Ctrl-C now if not intended.');
        }

        $output->writeln('Fetching all categories');
        $pool = $this->getAllCategories();

        $output->writeln('Building trees');
        $roots = $pool->getRoots();
        /** @var Category $root */
        foreach ($roots as $root) {
            $root->link($pool);
        }

        $hasCorruptions = false;

        foreach ($roots as $root) {
            $output->writeln("Checking root id={$root->getId()} code={$root->getCode()}");

            $fixedTree = $root->reorder();
            $corruptions = $root->diff($fixedTree);

            $rootHasCorruptions = (bool) count($corruptions);
            if ($rootHasCorruptions) {
                if ($inputOptionDumpCorruptions) {
                    $output->writeln($corruptions);
                }
                if ($inputOptionDumpFixedOrder) {
                    $output->writeln($fixedTree->dumpNodes(0, $inputArgumentMaxLevel));
                }
            }

            $corruptionStatus = count($corruptions) ? 'CORRUPTED' : 'SANE';

            $hasCorruptions |= $rootHasCorruptions;

            $output->writeln(
                "Root id={$root->getId()} code={$root->getCode()} is {$corruptionStatus}",
            );

            if ($rootHasCorruptions && $inputOptionReorder) {
                $output->writeln("UPDATING tree id={$root->getId()} code={$root->getCode()}!");
                $this->doUpdate($fixedTree);
            }
        }

        if ($inputOptionReorder && !$hasCorruptions) {
            $output->writeln('Requested update but no corruption found => nothing was done.');
        }

        return $hasCorruptions ? 1 : 0;
    }

    private function getAllCategories(): CategoriesPool
    {
        $sql = <<< SQL
SELECT id, parent_id, root, code, lvl, lft, rgt
FROM pim_catalog_category
ORDER BY lft
SQL;
        $rows = $this->connection->executeQuery($sql)->fetchAll();

        return new CategoriesPool($rows);
    }

    private function doUpdate(Category $root): void
    {
        if (!$this->connection->beginTransaction()) {
            throw new \Exception('Could not start update transaction');
        }

        try {
            $root->doUpdate($this->connection);
            if (!$this->connection->commit()) {
                throw new \Exception('Could not commit update transaction');
            }
        } catch (\Throwable $e) {
            $this->connection->rollBack();
        }
    }
}
