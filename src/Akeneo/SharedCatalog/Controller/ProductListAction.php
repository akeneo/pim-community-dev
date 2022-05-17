<?php

namespace Akeneo\SharedCatalog\Controller;

use Akeneo\SharedCatalog\Query\FindProductIdentifiersQueryInterface;
use Akeneo\SharedCatalog\Query\FindSharedCatalogQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductListAction
{
    public function __construct(
        private FindSharedCatalogQueryInterface $findSharedCatalogQuery,
        private FindProductIdentifiersQueryInterface $findProductIdentifiersQuery,
        private int $defaultPageSize
    ) {
    }

    public function __invoke(
        Request $request,
        string $sharedCatalogCode
    ): JsonResponse {
        $sharedCatalog = $this->findSharedCatalogQuery->find($sharedCatalogCode);
        if ($sharedCatalog === null) {
            throw new NotFoundHttpException("Catalog \"$sharedCatalogCode\" does not exist");
        }

        try {
            $identifiers = $this->findProductIdentifiersQuery->find(
                $sharedCatalog,
                [
                    'search_after' => $request->query->get('search_after', null),
                    'limit' => $request->query->getInt('limit', $this->defaultPageSize),
                ]
            );
        } catch (\InvalidArgumentException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }

        return new JsonResponse([
            'results' => $identifiers,
        ]);
    }
}
