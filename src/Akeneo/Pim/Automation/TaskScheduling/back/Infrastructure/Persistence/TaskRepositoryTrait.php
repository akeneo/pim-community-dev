<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Infrastructure\Persistence;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait TaskRepositoryTrait
{
    public function nextIdentity(): TaskId
    {
        return TaskId::fromString(Uuid::uuid4()->toString());
    }
}
