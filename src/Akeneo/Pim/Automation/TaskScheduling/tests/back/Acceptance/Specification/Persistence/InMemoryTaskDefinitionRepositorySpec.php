<?php

declare(strict_types=1);

namespace Specification\Akeneo\Test\Pim\Automation\TaskScheduling\Acceptance\Persistence;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskDefinition;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskId;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskSchedule;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryTaskDefinitionRepositorySpec extends ObjectBehavior
{
    function it_saves_a_new_task_definition()
    {
        $taskId = Uuid::uuid4()->toString();
        $task = TaskDefinition::create([
            'id' => TaskId::fromString($taskId),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => TaskSchedule::fromString('* * * * *'),
            'enabled' => true
        ]);

        $this->getAll()->shouldHaveCount(0);
        $this->save($task);
        $this->getAll()->shouldHaveKeyWithValue($taskId, $task);
    }

    function it_updates_an_existing_task_definition()
    {
        $taskId = Uuid::uuid4()->toString();
        $task = TaskDefinition::create([
            'id' => TaskId::fromString($taskId),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => TaskSchedule::fromString('* * * * *'),
            'enabled' => true
        ]);
        $this->save($task);
        $this->getAll()->shouldHaveKeyWithValue($taskId, $task);

        $updatedTask = $task->disable();
        $this->save($updatedTask);
        $this->getById($updatedTask->id())->shouldReturn($updatedTask);
        $this->getAll()->shouldNotContain($task);
    }

    function it_retrieves_a_task_definition_with_its_id()
    {
        $taskId = Uuid::uuid4()->toString();
        $task = TaskDefinition::create([
            'id' => TaskId::fromString($taskId),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => TaskSchedule::fromString('* * * * *'),
            'enabled' => true
        ]);
        $this->save($task);

        $this->getById(TaskId::fromString($taskId))->shouldReturn($task);
    }

    function it_retrieves_a_task_definition_with_its_code()
    {
        $task = TaskDefinition::create(
            [
                'id' => TaskId::fromString(Uuid::uuid4()->toString()),
                'code' => TaskCode::fromString('task_code'),
                'command' => TaskCommand::fromString('bin/console list'),
                'schedule' => TaskSchedule::fromString('* * * * *'),
                'enabled' => true
            ]
        );
        $this->save($task);

        $this->getByCode(TaskCode::fromString('task_code'))->shouldReturn($task);
    }
}
