<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\Category;


use Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks whether categories treee are sane or corrupted
 *  - checkes that (lft,right,lvl) is consistent with (parent_id,root_id) <- consided uncorrupted
 * @author    Weasels
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckCategoryTrees extends Command
{
    protected static $defaultName = 'pim:categories:check';

    /** @var Connection */
    private $connection;

    /** @var CategoryRepository */
    private $repository;

    public function __construct(
        Connection         $connection,
        CategoryRepository $repository

    )
    {
        parent::__construct();
        $this->connection = $connection;
        $this->repository = $repository;
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
                'dump-fixed-trees',
                't',
                InputOption::VALUE_NONE,
                'Whether we dump the corrected trees or not',
            )
            ->addOption("max-level",'m',InputArgument::OPTIONAL,"max level for tree dumping", 1)
            ->setDescription('Check all category trees against nested structure');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $dumpCorruptions= !!$input->getOption('dump-corruptions');

        $dumpFixedTrees = !!$input->getOption('dump-fixed-trees');

        $dumpFixedTreesMaxLevel = $input->getOption("max-level");

        // 1 - read all categories and build object trees

        echo "Fetching all categories\n";
        $pool = $this->getAllCategories();

        $roots = $pool->getRoots();

        echo "Building trees\n";
        foreach ($roots as $root) {
            $root->link($pool);
        }

        $hasCorruptions = false;

        foreach ($roots as $root) {
            $output->writeln("======================================");
            $output->writeln( "Checking root id={$root->getId()} code={$root->getCode()}");

            $fixedTree = $root->reorder();
            $corruptions = $root->diff($fixedTree);

            $rootHasCorruptions = !!count($corruptions);
            if ($rootHasCorruptions) {
                if ($dumpCorruptions) {
                    $output->writeln($corruptions);
                }
                if ($dumpFixedTrees) {
                    $output->writeln($fixedTree->dumpNodes(0, $dumpFixedTreesMaxLevel));
                }
            }

            $corruptionStatus = count($corruptions) ? 'CORRUPTED': 'SANE';

            $hasCorruptions |= $rootHasCorruptions;

            $output->writeln(
                "Root id={$root->getId()} code={$root->getCode()} is {$corruptionStatus} "
            );
        }

        return $hasCorruptions ? 1 : 0;
    }

    private function getAllCategories(): CategoriesPool
    {
        $sql = <<< SQL
SELECT id, parent_id, root, code, lvl, lft,rgt
FROM pim_catalog_category
ORDER BY lft
SQL;
        $rows = $this->connection->executeQuery($sql)->fetchAll();

        return new CategoriesPool($rows);
    }



}
