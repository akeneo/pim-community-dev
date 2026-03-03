<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobExecution\ListExportedFiles;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListExportedFilesQuery
{
    public function __construct(
        public int $jobExecutionId,
        public bool $withMedia = false,
    ) {
    }
}
