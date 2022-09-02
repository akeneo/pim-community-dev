<?php

namespace Akeneo\SharedCatalog\Controller;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\SharedCatalog\Query\FindSharedCatalogsQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SharedCatalogListAction
{
    public function __construct(private FindSharedCatalogsQueryInterface $findSharedCatalogsQuery)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $sharedCatalogs = $this->findSharedCatalogsQuery->execute();

        return new JsonResponse(array_map(static fn (SharedCatalog $sharedCatalog) => $sharedCatalog->normalizeForExternalApi(), $sharedCatalogs));
    }
}
