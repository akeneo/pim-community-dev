<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobByCodesInterface;

final class InMemoryDeleteJobByCodes implements DeleteJobByCodesInterface
{
    const JOBS = [
        ['code' => 'job_1'],
        ['code' => 'job_2'],
    ];

    private array $jobs = self::JOBS;

    public function delete(array $codes): void
    {
        foreach ($codes as $code) {
            foreach ($this->jobs as $key => $job) {
                if ($job['code']  === $code) {
                    unset($this->jobs[$key]);
                }
            }
        }
    }

    public function getJobs(): array
    {
        return $this->jobs;
    }

    public function reset(): void
    {
        $this->jobs = self::JOBS;
    }
}
