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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DuplicateProductController
{
    /** @var DuplicateProductHandler */
    private $duplicateProductHandler;

    /** @var ProductRepository */
    private $productRepository;

    /** @var NormalizerInterface */
    private $constraintViolationNormalizer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        DuplicateProductHandler $duplicateProductHandler,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->productRepository = $productRepository;
        $this->duplicateProductHandler = $duplicateProductHandler;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    public function duplicateProductAction(Request $request, string $id)
    {
        if (!$request->request->has('duplicated_product_identifier')) {
            throw new UnprocessableEntityHttpException('You should give either an "identifier" key.');
        }

        /** @var ProductInterface */
        $product = $this->productRepository->find($id);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product with id %s could not be found.', $id));
        }

        $duplicateProductCommand = new DuplicateProduct(
            $product->getIdentifier(),
            $request->request->get('duplicated_product_identifier')
        );

        $duplicateProductResponse = $this->duplicateProductHandler->handle($duplicateProductCommand);

        if ($duplicateProductResponse->isOk()) {
            return new JsonResponse(
                ['unique_attribute_codes' => $duplicateProductResponse->uniqueAttributeValues()],
                Response::HTTP_OK
            );
        }

        $normalizedViolations = [];
        foreach ($duplicateProductResponse->constraintViolationList() as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api'
            );
        }

        return new JsonResponse(
            ['values' => $normalizedViolations],
            Response::HTTP_BAD_REQUEST
        );
    }
}
