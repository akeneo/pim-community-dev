<?php

namespace Specification\Akeneo\Pim\Automation\TaskScheduling\Application\Task\CreateTask;

use Akeneo\Pim\Automation\TaskScheduling\Application\Task\CreateTask\CreateTaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Application\Task\CreateTask\CreateTaskHandler;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\Task;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class CreateTaskHandlerSpec extends ObjectBehavior
{
    function let(TaskRepository $taskRepository)
    {
        $this->beConstructedWith($taskRepository);
    }

    function it_is_a_create_task_handler()
    {
        $this->shouldHaveType(CreateTaskHandler::class);
    }

    function it_creates_and_persists_a_task(TaskRepository $taskRepository)
    {
        $command = new CreateTaskCommand('my_code', 'bin/console list', '* * * * *', true);

        $taskId = TaskId::fromString(Uuid::uuid4()->toString());
        $taskRepository->nextIdentity()->willReturn($taskId);
        $taskRepository->save(Argument::that(function (Task $task) use ($taskId): bool {
            $expectedCode = TaskCode::fromString('my_code');
            $expectedCommand = TaskCommand::fromString('bin/console list');

            return $task->code()->equals($expectedCode) && $task->command()->equals($expectedCommand) &&
                $task->schedule() === '* * * * *' && true === $task->isEnabled();
        }))->shouldBeCalled();

        $this->__invoke($command);
    }
}
