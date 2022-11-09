<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Syndication\Domain\Model\Catalog;
use Akeneo\Platform\Syndication\Domain\Query\Catalog\FindCatalogListQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetCatalogListAction
{
    private FindCatalogListQueryInterface $findCatalogListQuery;

    public function __construct(
        FindCatalogListQueryInterface $findCatalogListQuery
    ) {
        $this->findCatalogListQuery = $findCatalogListQuery;
    }

    public function __invoke(): JsonResponse
    {
        $catalogList = $this->findCatalogListQuery->execute();

        return new JsonResponse(array_map(function (Catalog $catalog) {
            return $catalog->normalize();
        }, $catalogList));
    }
}
