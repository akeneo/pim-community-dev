<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Domain\Model;

use Akeneo\Platform\Job\Domain\Model\JobItem;
use PhpSpec\ObjectBehavior;

class JobItemSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(JobItem::class);
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([]);
    }
}
