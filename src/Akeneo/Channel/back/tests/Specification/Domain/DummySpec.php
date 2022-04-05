<?php

namespace Specification\AkeneoEnterprise\Channel\Domain;

use AkeneoEnterprise\Channel\Domain\Dummy;
use PhpSpec\ObjectBehavior;

class DummySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Dummy::class);
    }
}
