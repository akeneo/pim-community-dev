<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Infrastructure\Cli;

use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Akeneo\UserManagement\Domain\Storage\AssignAllUsersToOneCategory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssignAllUsersToOneCategoryCommand extends Command
{
    protected static $defaultName = 'akeneo:user:assign-users-to-category-tree';

    public function __construct(
        private readonly AssignAllUsersToOneCategory $assignAllUsersToOneCategory,
        private readonly CategoryQueryInterface $categoryQuery,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Sets the default category tree to the tree (category code) passed as parameter for all users');
        $this->addOption(
            name: 'category-tree-code',
            mode: InputOption::VALUE_REQUIRED
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $categoryCode = $input->getOption('category-tree-code');
        try {
            $category = $this->categoryQuery->byCode($categoryCode);
        } catch (NotFoundHttpException $exception) {
            $output->writeln('The category passed as argument does not exist');

            return Command::INVALID;
        }

        if ($category->getParent() !== null) {
            $output->writeln('Cannot assign users to a category that is not a tree');

            return Command::INVALID;
        }

        $affectedUsers = $this->assignAllUsersToOneCategory->execute($category->getId());
        $output->writeln("$affectedUsers users' default tree updated");

        return Command::SUCCESS;
    }
}
