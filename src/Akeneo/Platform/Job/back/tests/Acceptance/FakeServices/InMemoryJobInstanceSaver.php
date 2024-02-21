<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class InMemoryJobInstanceSaver implements SaverInterface
{
    private array $jobInstances = [];

    /**
     * @param JobInstance $jobInstance
     */
    public function save($jobInstance, array $options = []): void
    {
        $this->jobInstances[$jobInstance->getCode()] = $jobInstance;
    }

    public function get(string $code): JobInstance
    {
        return $this->jobInstances[$code];
    }
}
