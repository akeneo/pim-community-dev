<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Application\TaskDefinition\Create;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskDefinition;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskSchedule;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskDefinitionRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTaskDefinitionHandler
{
    private TaskDefinitionRepository $taskRepository;

    public function __construct(TaskDefinitionRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function __invoke(CreateTaskDefinitionCommand $command): void
    {
        $task = TaskDefinition::create([
            'id' => $this->taskRepository->nextIdentity(),
            'code' => TaskCode::fromString($command->code()),
            'command' => TaskCommand::fromString($command->command()),
            'schedule' => TaskSchedule::fromString($command->schedule()),
            'enabled' => $command->enabled(),
        ]);
        $this->taskRepository->save($task);
    }
}
