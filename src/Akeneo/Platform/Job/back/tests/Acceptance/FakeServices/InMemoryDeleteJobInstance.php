<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobInstanceInterface;

final class InMemoryDeleteJobInstance implements DeleteJobInstanceInterface
{
    public const JOBS = [
        ['code' => 'job_1'],
        ['code' => 'job_2'],
    ];

    private array $jobs = self::JOBS;

    public function byCodes(array $codes): void
    {
        $this->jobs = array_filter($this->jobs, static fn (array $job) => !in_array($job['code'], $codes));
    }

    public function getJobs(): array
    {
        return $this->jobs;
    }

    public function getJobCodes(): array
    {
        return array_map(fn ($job) => $job['code'], $this->jobs);
    }
}
