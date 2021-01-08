<?php

namespace Specification\Akeneo\Pim\Automation\TaskScheduling\Application\TaskDefinition\Create;

use Akeneo\Pim\Automation\TaskScheduling\Application\TaskDefinition\Create\CreateTaskDefinitionCommand;
use Akeneo\Pim\Automation\TaskScheduling\Application\TaskDefinition\Create\CreateTaskDefinitionHandler;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskDefinition;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskId;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskDefinitionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class CreateTaskDefinitionHandlerSpec extends ObjectBehavior
{
    function let(TaskDefinitionRepository $taskRepository)
    {
        $this->beConstructedWith($taskRepository);
    }

    function it_is_a_create_task_definition_handler()
    {
        $this->shouldHaveType(CreateTaskDefinitionHandler::class);
    }

    function it_creates_and_persists_a_task_definition(TaskDefinitionRepository $taskRepository)
    {
        $command = new CreateTaskDefinitionCommand('my_code', 'bin/console list', '* * * * *', true);

        $taskId = TaskId::fromString(Uuid::uuid4()->toString());
        $taskRepository->nextIdentity()->willReturn($taskId);
        $taskRepository->save(Argument::that(function (TaskDefinition $task) use ($taskId): bool {
            $expectedCode = TaskCode::fromString('my_code');
            $expectedCommand = TaskCommand::fromString('bin/console list');

            return $task->code()->equals($expectedCode) && $task->command()->equals($expectedCommand) &&
                $task->schedule()->asString() === '* * * * *' && true === $task->isEnabled();
        }))->shouldBeCalled();

        $this->__invoke($command);
    }
}
