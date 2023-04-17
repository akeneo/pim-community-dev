<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Cli\RemoveOrphanCategories;

use Akeneo\Category\Infrastructure\Storage\Sql\PurgeOrphanCategoriesSql;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete all orphan categories with their children, those without parents and which aren't tree (id = root).
 *
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveOrphanCategories extends Command
{
    protected static $defaultName = 'akeneo:categories:remove-orphans';

    public function __construct(
        private readonly PurgeOrphanCategoriesSql $purgeOrphanCategories,
    ) {
        parent::__construct();
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
        $affectedRows = $this->purgeOrphanCategories->execute();
        $output->writeln("$affectedRows rows removed");

        return Command::SUCCESS;
    }
}
