<?php

namespace Akeneo\Platform\Job\Domain\Query\FindJobExecutionRows;

use Akeneo\Platform\Job\Application\SearchJobExecutionTable\JobExecutionRow;
use Webmozart\Assert\Assert;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindJobExecutionRowsResult
{
    public array $jobExecutionRows;
    public int $matchesCount;

    public function __construct(array $jobExecutionRows, int $matchesCount)
    {
        Assert::allIsInstanceOf($jobExecutionRows, JobExecutionRow::class);

        $this->jobExecutionRows = $jobExecutionRows;
        $this->matchesCount = $matchesCount;
    }
}
