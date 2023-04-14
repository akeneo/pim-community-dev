<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Cli\RemoveOrphanCategories;

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
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveOrphanCategories extends Command
{
    protected static $defaultName = 'akeneo:categories:remove-orphans';

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
        $this->setDescription('Remove categories that have no parent and aren\'t category trees');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $affectedRows =$this->deleteOrphanCategories();
        $output->writeln("$affectedRows rows removed");
        return Command::SUCCESS;
    }

    private function deleteOrphanCategories(): int
    {
        $sql = <<< SQL
            WITH RECURSIVE orphan_categories as (
                SELECT id, code, parent_id
                FROM pim_catalog_category AS children
                WHERE parent_id IS NULL AND root != id
                UNION
                SELECT pim_catalog_category.id, pim_catalog_category.code, pim_catalog_category.parent_id
                FROM pim_catalog_category
                INNER JOIN orphan_categories parent ON parent.id = pim_catalog_category.parent_id
            )
            DELETE
            FROM pim_catalog_category
            USING pim_catalog_category
            JOIN orphan_categories ON pim_catalog_category.id = orphan_categories.id;
        SQL;
        return $this->connection->executeQuery($sql)->rowCount();
    }
}
