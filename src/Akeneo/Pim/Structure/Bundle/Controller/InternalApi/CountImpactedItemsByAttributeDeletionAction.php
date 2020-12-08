<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CountImpactedItemsByAttributeDeletionAction
{
    private CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute;
    private CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute;

    public function __construct(
        CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute,
        CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute
    ) {
        $this->countProductsWithRemovedAttribute = $countProductsWithRemovedAttribute;
        $this->countProductModelsWithRemovedAttribute = $countProductModelsWithRemovedAttribute;
    }

    public function __invoke(string $code): Response
    {
        $productCount = $this->countProductsWithRemovedAttribute->count([$code]);
        $productModelCount = $this->countProductModelsWithRemovedAttribute->count([$code]);

        return new JsonResponse([
            'products' => $productCount,
            'product_models' => $productModelCount,
        ]);
    }
}
