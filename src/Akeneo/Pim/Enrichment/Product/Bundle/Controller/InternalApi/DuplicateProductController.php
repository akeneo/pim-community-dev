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

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProduct;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProductHandler;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use LogicException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DuplicateProductController
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private DuplicateProductHandler $duplicateProductHandler,
        private NormalizerInterface $constraintViolationNormalizer,
        private UserContext $userContext,
        private NormalizerInterface $normalizer
    ) {
    }

    public function duplicateProductAction(Request $request, string $uuid): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['duplicated_product_identifier'])) {
            throw new UnprocessableEntityHttpException('You should give a "duplicated_product_identifier" key.');
        }

        /** @var ProductInterface */
        $product = $this->productRepository->find($uuid);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product with uuid %s could not be found.', $uuid));
        }

        if (null === $this->userContext->getUser()) {
            throw new LogicException('No authenticated user found.');
        }

        $duplicatedProductIdentifier = $data['duplicated_product_identifier'] ?? null;
        $duplicateProductCommand = new DuplicateProduct(
            $product->getUuid(),
            $duplicatedProductIdentifier,
            $this->userContext->getUser()->getId()
        );

        try {
            $duplicateProductResponse = $this->duplicateProductHandler->handle($duplicateProductCommand);
        } catch (ObjectNotFoundException $exception) {
            throw new AccessDeniedException();
        }


        if ($duplicateProductResponse->isOk()) {
            return new JsonResponse(
                [
                    'duplicated_product' => $this->normalizer->normalize(
                        $duplicateProductResponse->duplicatedProduct(),
                        'internal_api'
                    ),
                    'unique_attribute_codes' => $duplicateProductResponse->uniqueAttributeValues()
                ],
                Response::HTTP_OK
            );
        }

        $normalizedViolations = [];
        foreach ($duplicateProductResponse->constraintViolationList() as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product' => $product]
            );
        }

        return new JsonResponse(
            ['values' => $normalizedViolations],
            Response::HTTP_BAD_REQUEST
        );
    }
}
