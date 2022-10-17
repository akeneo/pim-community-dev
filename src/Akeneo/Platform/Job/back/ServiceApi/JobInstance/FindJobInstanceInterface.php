<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobInstance;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FindJobInstanceInterface
{
    /**
     * @return array<JobInstance>
     */
    public function fromQuery(JobInstanceQuery $query): array;
}
