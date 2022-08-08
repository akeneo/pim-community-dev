<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceAPI\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindJobInstancesWithAutomation
{
    /**
     * @return JobInstanceWithAutomation[]
     */
    public function fromQuery(JobInstanceQuery $query): array;
}
