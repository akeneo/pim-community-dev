<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\CacheClearer;
use PhpSpec\ObjectBehavior;

class CacheClearerSpec extends ObjectBehavior
{
    public function it_is_a_cache_clearer(): void
    {
        $this->shouldHaveType(CacheClearer::class);
        $this->shouldImplement(CacheClearerInterface::class);
    }
}
