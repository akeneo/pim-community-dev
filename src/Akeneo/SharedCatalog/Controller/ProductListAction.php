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
    /** @var FindSharedCatalogQueryInterface */
    private $findSharedCatalogQuery;

    /** @var FindProductIdentifiersQueryInterface */
    private $findProductIdentifiersQuery;

    /** @var int */
    private $defaultPageSize;

    public function __construct(
        FindSharedCatalogQueryInterface $findSharedCatalogQuery,
        FindProductIdentifiersQueryInterface $findProductIdentifiersQuery,
        int $defaultPageSize
    ) {
        $this->findSharedCatalogQuery = $findSharedCatalogQuery;
        $this->findProductIdentifiersQuery = $findProductIdentifiersQuery;
        $this->defaultPageSize = $defaultPageSize;
    }

    public function __invoke(
        Request $request,
        string $sharedCatalogCode
    ): JsonResponse {
        $sharedCatalog = $this->findSharedCatalogQuery->find($sharedCatalogCode);
        if (!$sharedCatalog) {
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
