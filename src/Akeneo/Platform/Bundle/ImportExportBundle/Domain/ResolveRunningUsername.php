<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Domain;

class ResolveRunningUsername
{
    private const AUTOMATED_USER_PREFIX = 'job_automated_';

    public function fromJobCode(string $jobCode): string
    {
        return sprintf('%s%s', self::AUTOMATED_USER_PREFIX, $jobCode);
    }
}
