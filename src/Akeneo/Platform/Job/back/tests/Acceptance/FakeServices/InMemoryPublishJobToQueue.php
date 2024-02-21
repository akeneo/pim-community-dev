<?php

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueueInterface;

class InMemoryPublishJobToQueue implements PublishJobToQueueInterface
{
    private int $lastId = 0;

    public function publish(string $jobInstanceCode, array $config, bool $noLog = false, ?string $username = null, ?array $emails = []): JobExecution
    {
        return new FakeJobExecution(++$this->lastId);
    }

    public function getLastId(): int
    {
        return $this->lastId;
    }
}
