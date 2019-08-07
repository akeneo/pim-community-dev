<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Query;

/**
 * Deletes the job execution messages whose job execution doesn't exist anymore.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DeleteJobExecutionMessageOrphansQueryInterface
{
    public function execute(): void;
}
