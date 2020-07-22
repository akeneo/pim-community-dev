<?php

namespace Akeneo\SharedCatalog\Controller;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\SharedCatalog\Query\FindSharedCatalogQueryInterface;
use Akeneo\SharedCatalog\Query\GetProductIdFromProductIdentifierQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductListAction
{
    /** @var FindSharedCatalogQueryInterface */
    private $findSharedCatalogQuery;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var GetProductIdFromProductIdentifierQuery */
    private $getProductIdFromProductIdentifierQuery;

    /** @var int */
    private $defaultPageSize;

    public function __construct(
        FindSharedCatalogQueryInterface $findSharedCatalogQuery,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        GetProductIdFromProductIdentifierQuery $getProductIdFromProductIdentifierQuery,
        int $defaultPageSize
    ) {
        $this->findSharedCatalogQuery = $findSharedCatalogQuery;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->getProductIdFromProductIdentifierQuery = $getProductIdFromProductIdentifierQuery;
        $this->defaultPageSize = $defaultPageSize;
    }

    public function __invoke(
        Request $request,
        string $sharedCatalogCode
    ): JsonResponse {
        $sharedCatalog = $this->findSharedCatalogQuery->find($sharedCatalogCode);
        if (!$sharedCatalog) {
            throw new NotFoundHttpException();
        }

        $pqbOptions = [
            'default_scope' => $sharedCatalog->filters['structure']['scope'],
            'filters' => $sharedCatalog->filters['data'] ?? [],
            'limit' => $request->query->getInt('limit', $this->defaultPageSize),
        ];

        $searchAfterProductIdentifier = $request->get('search_after', null);

        if (null !== $searchAfterProductIdentifier) {
            $searchAfterProductId = $this->getProductIdFromProductIdentifierQuery->execute($searchAfterProductIdentifier);

            if (null === $searchAfterProductId) {
                throw new BadRequestHttpException(sprintf('Product with identifier "%s" not found', $searchAfterProductIdentifier));
            }

            $pqbOptions['search_after'] = [$searchAfterProductIdentifier, 'product_' . $searchAfterProductId];
        }

        $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
        $pqb->addSorter('identifier', Directions::ASCENDING);

        $results = $pqb->execute();

        $identifiers = array_map(function (IdentifierResult $result) {
            return $result->getIdentifier();
        }, iterator_to_array($results));

        return new JsonResponse([
            'results' => $identifiers,
        ]);
    }
}
