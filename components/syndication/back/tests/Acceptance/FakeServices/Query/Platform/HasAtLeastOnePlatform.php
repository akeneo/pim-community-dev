<?php

namespace Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Query\Platform;

use Akeneo\Platform\Syndication\Domain\Query\Platform\HasAtLeastOnePlatformInterface;

class HasAtLeastOnePlatform implements HasAtLeastOnePlatformInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute(): bool
    {
        return false;
    }
}
