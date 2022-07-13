<?php

namespace Akeneo\SharedCatalog\Controller;

use Akeneo\SharedCatalog\Query\FindProductUuidsQueryInterface;
use Akeneo\SharedCatalog\Query\FindSharedCatalogQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductUuidListAction
{
    public function __construct(
        private FindSharedCatalogQueryInterface $findSharedCatalogQuery,
        private FindProductUuidsQueryInterface $findProductUuidsQuery,
        private int $defaultPageSize
    ) {
    }

    public function __invoke(Request $request, string $sharedCatalogCode): JsonResponse
    {
        $sharedCatalog = $this->findSharedCatalogQuery->find($sharedCatalogCode);
        if ($sharedCatalog === null) {
            throw new NotFoundHttpException(\sprintf('Catalog "%s" does not exist', $sharedCatalogCode));
        }

        try {
            $uuids = $this->findProductUuidsQuery->find(
                $sharedCatalog,
                [
                    'search_after' => $request->query->get('search_after', null),
                    'limit' => $request->query->getInt('limit', $this->defaultPageSize),
                ]
            );
        } catch (\InvalidArgumentException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }

        return new JsonResponse(['results' => $uuids]);
    }
}
