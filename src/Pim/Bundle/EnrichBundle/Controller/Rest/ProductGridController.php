<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductGridController {
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->familyRepository = $familyRepository;
    }

    public function indexAction(Request $request): JsonResponse
    {
        $searchOptions = json_decode($request->getContent(), true);
        $pqb = $this->pqbFactory->create($searchOptions);
        $cursor = $pqb->execute();

        $products = [];
        while ($cursor->valid()) {
            $product = $cursor->current();
            $family = $this->familyRepository->findOneBy(['id' => $product['family_id']]);
            $product['values'] = json_decode($product['raw_values'], true);
            $products[] = $product;
            $cursor->next();
        }

        return new JsonResponse($products);
    }

    protected function getLabels(array $values, FamilyInterface $family): array
    {

    }
}
