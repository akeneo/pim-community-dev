<?php

namespace Akeneo\SharedCatalog\Controller;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\SharedCatalog\Query\FindSharedCatalogQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductListAction
{
    /** @var FindSharedCatalogQueryInterface */
    private $findSharedCatalogQuery;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var int */
    private $pageSize;

    public function __construct(
        FindSharedCatalogQueryInterface $findSharedCatalogQuery,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        int $pageSize
    ) {
        $this->findSharedCatalogQuery = $findSharedCatalogQuery;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->pageSize = $pageSize;
    }

    public function __invoke(
        Request $request,
        string $shared_catalog_code
    ): JsonResponse {
        $sharedCatalog = $this->findSharedCatalogQuery->find($shared_catalog_code);

        if (!$sharedCatalog) {
            throw new NotFoundHttpException();
        }

        $page = (int) $request->get('page', 1);

        $pqb = $this->productQueryBuilderFactory->create([
            'default_scope' => $sharedCatalog->filters['structure']['scope'],
            'filters' => $sharedCatalog->filters['data'] ?? [],
            'limit' => $this->pageSize,
            'from' => ($page - 1) * $this->pageSize,
        ]);
        $pqb->addSorter('identifier', Directions::ASCENDING);

        $results = $pqb->execute();

        $identifiers = array_map(function (IdentifierResult $result) {
            return $result->getIdentifier();
        }, iterator_to_array($results));

        return new JsonResponse([
            'results' => $identifiers,
            'pagination' => [
                'results_by_page' => $this->pageSize,
                'results_count' => count($identifiers),
                'total_count' => $results->count(),
            ],
        ]);
    }
}
