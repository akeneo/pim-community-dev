<?php

declare(strict_types=1);

namespace Specification\Akeneo\Test\Pim\Automation\TaskScheduling\Acceptance\Persistence;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\Task;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryTaskRepositorySpec extends ObjectBehavior
{
    function it_saves_a_new_task()
    {
        $taskId = Uuid::uuid4()->toString();
        $task = Task::create([
            'id' => TaskId::fromString($taskId),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => '* * * * *',
            'enabled' => true
        ]);

        $this->getAll()->shouldHaveCount(0);
        $this->save($task);
        $this->getAll()->shouldHaveKeyWithValue($taskId, $task);
    }

    function it_updates_an_existing_task()
    {
        $taskId = Uuid::uuid4()->toString();
        $task = Task::create([
            'id' => TaskId::fromString($taskId),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => '* * * * *',
            'enabled' => true
        ]);
        $this->save($task);
        $this->getAll()->shouldHaveKeyWithValue($taskId, $task);

        $updatedTask = $task->disable();
        $this->save($updatedTask);
        $this->getById($updatedTask->id())->shouldReturn($updatedTask);
        $this->getAll()->shouldNotContain($task);
    }

    function it_retrieves_a_task_with_its_id()
    {
        $taskId = Uuid::uuid4()->toString();
        $task = Task::create([
            'id' => TaskId::fromString($taskId),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => '* * * * *',
            'enabled' => true
        ]);
        $this->save($task);

        $this->getById(TaskId::fromString($taskId))->shouldReturn($task);
    }
}
