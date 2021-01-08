<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Repository;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\Task;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TaskRepository
{
    public function save(Task $task): void;

    /**
     * @param TaskId $id
     * @return Task
     * @throw TaskNotFoundException
     */
    public function getById(TaskId $id): Task;

    /**
     * @param string $code
     * @return Task
     * @throw TaskNotFoundException
     */
    public function getByCode(string $code): Task;

    public function nextIdentity(): TaskId;
}
