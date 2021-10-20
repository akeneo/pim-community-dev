<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Domain;

use Akeneo\Platform\Job\Domain\Dummy;
use PhpSpec\ObjectBehavior;

class DummySpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(Dummy::class);
    }
}
