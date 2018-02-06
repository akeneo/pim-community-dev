<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductGridController {
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        FamilyRepositoryInterface $familyRepository,
        NormalizerInterface $productNormalizer,
        NormalizerInterface $productModelNormalizer
    ) {
        $this->pqbFactory             = $pqbFactory;
        $this->familyRepository       = $familyRepository;
        $this->productNormalizer      = $productNormalizer;
        $this->productModelNormalizer = $productModelNormalizer;
    }

    public function indexAction(Request $request): JsonResponse
    {
        $searchOptions = $request->query->all();
        $searchOptions['limit'] = (int) $searchOptions['limit'];
        $searchOptions['from'] = (int) $searchOptions['from'];

        $pqb = $this->pqbFactory->create($searchOptions);
        $cursor = $pqb->execute();

        $products = [];
        while ($cursor->valid()) {
            $product = $cursor->current();

            $normalizer = $product instanceof ProductInterface ?
                $this->productNormalizer :
                $this->productModelNormalizer;
            $normalizedProduct = $normalizer->normalize(
                $product,
                'internal_api',
                [
                    'locales' => ['en_US'],
                    'channels' => ['ecommerce'],
                    'data_locale' => 'en_US'
                ]
            );
            $products[] = $normalizedProduct;
            $cursor->next();
        }

        return new JsonResponse(['items' => $products, 'total' => count($cursor)]);
    }
}
