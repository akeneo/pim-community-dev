<?php

namespace Akeneo\SharedCatalog\Controller;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\SharedCatalog\Query\FindSharedCatalogsQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SharedCatalogListAction
{
    /** @var FindSharedCatalogsQueryInterface */
    private $findSharedCatalogsQuery;

    public function __construct(
        FindSharedCatalogsQueryInterface $findSharedCatalogsQuery
    ) {
        $this->findSharedCatalogsQuery = $findSharedCatalogsQuery;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $sharedCatalogs = $this->findSharedCatalogsQuery->execute();

        return new JsonResponse(array_map(function (SharedCatalog $sharedCatalog) {
            return $sharedCatalog->normalizeForExternalApi();
        }, $sharedCatalogs));
    }
}
