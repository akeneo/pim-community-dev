<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application;

use Akeneo\Platform\Job\Application\JobExecutionRow;
use PhpSpec\ObjectBehavior;

class JobExecutionRowSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(JobExecutionRow::class);
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([]);
    }
}
