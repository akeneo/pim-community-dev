<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Service;

use PhpSpec\ObjectBehavior;

class IpMatcherSpec extends ObjectBehavior
{
    public function it_does_not_match_if_the_whitelist_is_empty(): void
    {
        $this->match('168.212.226.204', [])->shouldReturn(false);
    }

    public function it_does_not_match_if_not_in_the_whitelist(): void
    {
        $this->match('168.212.226.204', ['10.0.0.0'])->shouldReturn(false);
    }

    public function it_match_if_in_the_whitelist(): void
    {
        $this->match('168.212.226.204', ['168.212.226.0/24'])->shouldReturn(true);
    }
}
