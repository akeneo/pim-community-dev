<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Cache;

interface CachedQueriesClearerInterface
{
    public function clear(): void;
}
