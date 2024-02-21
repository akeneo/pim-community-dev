<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobInstance;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JobInstanceQuery
{
    public function __construct(
        public ?array $jobNames = null,
        public ?string $search = null,
        public ?JobInstanceQueryPagination $pagination = null,
    ) {
    }
}
