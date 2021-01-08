<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\TaskScheduling\Acceptance\Persistence;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskDefinition;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskId;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskNotFoundException;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskDefinitionRepository;
use Akeneo\Pim\Automation\TaskScheduling\Infrastructure\Persistence\TaskDefinitionRepositoryTrait;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryTaskDefinitionRepository implements TaskDefinitionRepository
{
    use TaskDefinitionRepositoryTrait;

    /** @var TaskDefinition[] */
    private array $tasks = [];

    public function save(TaskDefinition $task): void
    {
        $this->tasks[$task->id()->asString()] = $task;
    }

    public function getById(TaskId $id): TaskDefinition
    {
        $task = $this->tasks[$id->asString()] ?? null;
        if (null === $task) {
            throw TaskNotFoundException::withId($id);
        }

        return $task;
    }

    public function getByCode(TaskCode $code): TaskDefinition
    {
        foreach ($this->tasks as $task) {
            if ($task->code()->equals($code)) {
                return $task;
            }
        }

        throw TaskNotFoundException::withCode($code);
    }

    /**
     * @return TaskDefinition[]
     */
    public function getAll(): array
    {
        return $this->tasks;
    }
}
