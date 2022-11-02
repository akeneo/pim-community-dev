<?php

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

class FakeJobExecution extends JobExecution
{
    public function __construct(
        private int $fakeId,
    ) {
        parent::__construct();
    }

    public function getId(): int
    {
        return $this->fakeId;
    }
}
