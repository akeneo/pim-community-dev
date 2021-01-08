<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Application\Task\CreateTask;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\Task;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskSchedule;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTaskHandler
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function __invoke(CreateTaskCommand $command): void
    {
        $task = Task::create([
            'id' => $this->taskRepository->nextIdentity(),
            'code' => TaskCode::fromString($command->code()),
            'command' => TaskCommand::fromString($command->command()),
            'schedule' => TaskSchedule::fromString($command->schedule()),
            'enabled' => $command->enabled(),
        ]);
        $this->taskRepository->save($task);
    }
}
