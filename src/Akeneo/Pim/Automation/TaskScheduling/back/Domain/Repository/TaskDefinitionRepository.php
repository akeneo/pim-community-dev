<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Repository;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskDefinition;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskId;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TaskDefinitionRepository
{
    public function save(TaskDefinition $task): void;

    /**
     * @param TaskId $id
     *
     * @return TaskDefinition
     * @throw TaskNotFoundException
     */
    public function getById(TaskId $id): TaskDefinition;

    /**
     * @param TaskCode $code
     *
     * @return TaskDefinition
     * @throw TaskNotFoundException
     */
    public function getByCode(TaskCode $code): TaskDefinition;

    public function nextIdentity(): TaskId;
}
