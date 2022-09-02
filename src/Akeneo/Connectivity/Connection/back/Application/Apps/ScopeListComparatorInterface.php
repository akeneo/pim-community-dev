<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps;

interface ScopeListComparatorInterface
{
    /**
     * @param array<string> $source
     * @param array<string> $filter
     * @return array<string>
     */
    public function diff(array $source, array $filter): array;
}
