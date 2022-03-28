<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;

class ScopeListComparator implements ScopeListComparatorInterface
{
    public function __construct(
        private ScopeMapperRegistry $scopeMapperRegistry,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function diff(array $source, array $filter): array
    {
        $originalScopeList = $this->scopeMapperRegistry->getExhaustiveScopes($source);
        $requestedScopeList = $this->scopeMapperRegistry->getExhaustiveScopes($filter);

        $newScopes = \array_unique(\array_diff($originalScopeList, $requestedScopeList));

        \sort($newScopes);

        return $newScopes;
    }
}
