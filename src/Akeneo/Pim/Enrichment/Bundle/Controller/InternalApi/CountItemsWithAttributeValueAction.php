<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CountItemsWithAttributeValueAction
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

    public function __invoke(Request $request): Response
    {
        $code = $request->get('attribute_code');
        if ($code === null) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $productCount = $this->countProductsWithRemovedAttribute->count([$code]);
        $productModelCount = $this->countProductModelsWithRemovedAttribute->count([$code]);

        return new JsonResponse([
            'products' => $productCount,
            'product_models' => $productModelCount,
        ]);
    }
}
