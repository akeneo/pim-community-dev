<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\back\Infrastructure\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTasksToExecuteCommand extends Command
{
    protected static $defaultName = 'pim:task-scheduling:get-tasks-to-execute';

    protected function configure(): void
    {
        $this->setDescription('Get the list of tasks to execute and add them to the queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('OK!');

        return 0;
    }
}
