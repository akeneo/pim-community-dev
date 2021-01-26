<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Cache;

interface CachedQueryInterface
{
    public function clearCache(): void;
}
