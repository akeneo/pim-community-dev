<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\Category;


use Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        Connection $connection,
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
//            ->addArgument(
//                'identifiers',
//                InputArgument::REQUIRED,
//                'The product identifiers to clean (comma separated values)'
//            )
//            ->setHidden(true)
            ->setDescription('Check all category trees agains nested structure');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        if (false) {
            $errors = $this->repository->verify();
            if ($errors === false) {
                echo "FINE!!\n";
            } else {
                var_export($errors);
            }
        }

        // TODO lock tables !


           $this->repository->reorderAll(null, 'ASC', false);

        // TODO unlock tables !

        echo "Reordered!!\n";

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute2(InputInterface $input, OutputInterface $output): int
    {

        // 1 - read all categories and build object trees

        $pool = $this->getAllCategories();

        $roots = $pool->getRoots();

        foreach($roots as $root) {
            $root->link($pool);
        }



        // 2 - traversing trees
        // for reach node
        // check that
        // - (node.lft,node.rgt) = (min(child.lft)-1, max(child.right)+1)
        // - child[i].rgt = child[i+1].lft - 1

        // on each violation report


        foreach($roots as $root) {
            $this->checkRoot($root);
        }

        return 0;

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

    private function checkRoot(Category $c) {
        echo "Checking root [{$c->getCode()}]\n";



        $this->checkCategory($c);
}

    private function computeCorrectlyNestedCategory(Category $c) {
        echo "checking category id={$c->getId()}\n";

        $violations = $c->diff($c->computeNested());

        foreach($violations as $v) {
            echo "{v}\n";
        }
    }

}
