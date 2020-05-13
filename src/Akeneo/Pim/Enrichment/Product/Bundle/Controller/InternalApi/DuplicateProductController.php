<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProduct;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProductHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DuplicateProductController
{
    /** @var DuplicateProductWithoutUniqueValuesHandler */
    private $duplicateProductHandler;

    /** @var ProductRepository */
    private $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        DuplicateProductHandler $duplicateProductHandler
    ) {
        $this->productRepository = $productRepository;
        $this->duplicateProductHandler = $duplicateProductHandler;
    }

    public function duplicateProductAction(Request $request, $id)
    {
        if (!$request->request->has('duplicated_product_identifier')) {
            throw new UnprocessableEntityHttpException('You should give either an "identifier" key.');
        }

        /** @var ProductInterface */
        $product = $this->productRepository->find($id);

        $query = new DuplicateProduct(
            $product->getIdentifier(),
            $request->request->get('duplicated_product_identifier')
        );

        $duplicateProductResponse = $this->duplicateProductHandler->handle($query);

        return new JsonResponse(
            ['unique_attribute_codes' => $duplicateProductResponse->uniqueAttributeValues()],
            Response::HTTP_OK
        );
    }
}
