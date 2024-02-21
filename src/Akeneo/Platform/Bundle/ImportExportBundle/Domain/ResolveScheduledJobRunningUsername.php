<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Domain;

class ResolveScheduledJobRunningUsername
{
    public const AUTOMATED_USER_PREFIX = 'job_automated_';

    public function fromJobCode(string $jobCode): string
    {
        return sprintf('%s%s', self::AUTOMATED_USER_PREFIX, $jobCode);
    }
}
