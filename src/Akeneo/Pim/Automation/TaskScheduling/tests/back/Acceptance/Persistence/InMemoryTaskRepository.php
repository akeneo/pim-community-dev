<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\TaskScheduling\Acceptance\Persistence;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\Task;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskNotFoundException;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskRepository;
use Akeneo\Pim\Automation\TaskScheduling\Infrastructure\Persistence\TaskRepositoryTrait;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryTaskRepository implements TaskRepository
{
    use TaskRepositoryTrait;

    /** @var Task[] */
    private array $tasks = [];

    public function save(Task $task): void
    {
        $this->tasks[$task->id()->asString()] = $task;
    }

    public function getById(TaskId $id): Task
    {
        $task = $this->tasks[$id->asString()] ?? null;
        if (null === $task) {
            throw TaskNotFoundException::withId($id);
        }

        return $task;
    }

    public function getByCode(string $code): Task
    {
        foreach ($this->tasks as $task) {
            if ($task->code() === $code) {
                return $task;
            }
        }

        throw TaskNotFoundException::withCode($code);
    }

    /**
     * @return Task[]
     */
    public function getAll(): array
    {
        return $this->tasks;
    }
}
