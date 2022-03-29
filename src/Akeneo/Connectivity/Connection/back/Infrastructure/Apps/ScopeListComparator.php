<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;

class ScopeListComparator implements ScopeListComparatorInterface
{
    public function __construct(
        private ScopeMapperRegistry $scopeMapperRegistry
    ) {
    }

    /**
     * @inheritDoc
     */
    public function diff(array $source, array $filter): array
    {
        $exhaustiveSourceScopeList = $this->scopeMapperRegistry->getExhaustiveScopes($source);
        $exhaustiveFilterScopeList = $this->scopeMapperRegistry->getExhaustiveScopes($filter);

        $newScopes = \array_unique(\array_diff($exhaustiveSourceScopeList, $exhaustiveFilterScopeList));

        \sort($newScopes);

        return $newScopes;
    }
}
