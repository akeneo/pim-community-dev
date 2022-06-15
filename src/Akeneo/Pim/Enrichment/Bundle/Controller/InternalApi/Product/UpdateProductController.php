<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\Product;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateProductController
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ObjectUpdaterInterface $productUpdater,
        private SaverInterface $productSaver,
        private NormalizerInterface $normalizer,
        private ValidatorInterface $validator,
        private UserContext $userContext,
        private CollectionFilterInterface $productEditDataFilter,
        private AttributeConverterInterface $localizedConverter,
        private FilterInterface $emptyValuesFilter,
        private ConverterInterface $productValueConverter,
        private NormalizerInterface $constraintViolationNormalizer,
        private AttributeFilterInterface $productAttributeFilter,
        private MessageBusInterface $commandMessageBus,
        private MessageBusInterface $queryMessageBus
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
        $data = json_decode($request->getContent(), true);
        try {
            $data = $this->productEditDataFilter->filterCollection($data, null, ['product' => $product]);
        } catch (ObjectNotFoundException) {
            throw new BadRequestHttpException();
        }
        try {
            $this->updateProduct($product, $data);
        } catch (ViolationsException | LegacyViolationsException $e) {
            $isNotOwnerException = \count(
                    \array_filter(
                        \iterator_to_array($e->violations()),
                        fn (ConstraintViolationInterface $violation): bool => ViolationCode::containsViolationCode((int) $violation->getCode(), ViolationCode::USER_IS_NOT_OWNER)
                    )
                ) > 0;
            if ($isNotOwnerException) {
                return $this->handleProductDraft($product, $data);
            }

            $hasPermissionException = \count(
                    \array_filter(
                        \iterator_to_array($e->violations()),
                        function (ConstraintViolationInterface $violation): bool {
                            return \is_int($violation->getCode()) && ViolationCode::containsViolationCode((int)$violation->getCode(), ViolationCode::PERMISSION);
                        }
                    )
                ) > 0;
            if ($hasPermissionException) {
                throw new AccessDeniedHttpException();
            }
            $product = $this->findProductOr404($id);
            $violations = $e->violations();
            $violations->addAll($this->localizedConverter->getViolations());
            $normalizedViolations = $this->normalizeViolations($violations, $product);

            return new JsonResponse($normalizedViolations, 400);
        } catch (TwoWayAssociationWithTheSameProductException $e) {
            return new JsonResponse(
                [
                    'message' => $e->getMessage(),
                    'global' => true],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $localizedConverterViolations = $this->localizedConverter->getViolations();
        if (\count($localizedConverterViolations) > 0) {
            $normalizedViolations = $this->normalizeViolations($localizedConverterViolations, $product);

            return new JsonResponse($normalizedViolations, 400);
        }

        $product = $this->findProductOr404($id);
        $normalizedProduct = $this->normalizer->normalize(
            $product,
            'internal_api',
            $this->getNormalizationContext()
        );

        return new JsonResponse($normalizedProduct);

    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param string $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    private function findProductOr404($id)
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', $id)
            );
        }

        return $product;
    }

    /**
     * Updates product with the provided request data
     *
     * @param ProductInterface $product
     * @param array            $data
     */
    private function updateProduct(ProductInterface $product, array $data)
    {
        $values = $this->productValueConverter->convert($data['values']);

        $values = $this->localizedConverter->convertToDefaultFormats($values, [
            'locale' => $this->userContext->getUiLocale()->getCode()
        ]);

        $dataFiltered = $this->emptyValuesFilter->filter($product, ['values' => $values]);

        if (!empty($dataFiltered)) {
            $data = array_replace($data, $dataFiltered);
        } else {
            $data['values'] = [];
        }

        // don't filter during creation, because identifier is needed
        // but not sent by the frontend during creation (it sends the sku in the values)
        if (null !== $product->getId() && $product->isVariant()) {
            $data = $this->productAttributeFilter->filter($data);
        }

        $envelope = $this->queryMessageBus->dispatch(new GetUserIntentsFromStandardFormat($data));
        $handledStamp = $envelope->last(HandledStamp::class);
        $userIntents = $handledStamp->getResult();

        $userId = $this->userContext->getUser()?->getId();
        $command = UpsertProductCommand::createFromCollection(
            $userId,
            $product->getIdentifier() ?? '',
            $userIntents
        );
        $this->commandMessageBus->dispatch($command);
    }

    /**
     * Updates product or draft with the provided request data. The product should be updated through the service API
     * so this is only used for draft.
     */
    private function updateDraft(ProductInterface $product, array $data): void
    {
        $values = $this->productValueConverter->convert($data['values']);

        $values = $this->localizedConverter->convertToDefaultFormats($values, [
            'locale' => $this->userContext->getUiLocale()->getCode()
        ]);

        $dataFiltered = $this->emptyValuesFilter->filter($product, ['values' => $values]);

        if (!empty($dataFiltered)) {
            $data = array_replace($data, $dataFiltered);
        } else {
            $data['values'] = [];
        }

        // don't filter during creation, because identifier is needed
        // but not sent by the frontend during creation (it sends the sku in the values)
        if (null !== $product->getId() && $product->isVariant()) {
            $data = $this->productAttributeFilter->filter($data);
        }

        $this->productUpdater->update($product, $data);
    }

    /**
     * Get the context used for product normalization
     *
     * @return array
     */
    private function getNormalizationContext(): array
    {
        return $this->userContext->toArray() + ['filter_types' => []];
    }

    private function normalizeViolations(ConstraintViolationListInterface $violations, ProductInterface $product): array
    {
        $normalizedViolations = [
            'values' => [],
        ];

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();

            if (0 === strpos($propertyPath, 'quantifiedAssociations.')) {
                $normalizedViolations['quantified_associations'][] = $this->normalizer->normalize(
                    $violation,
                    'internal_api',
                    ['translate' => false]
                );
                continue;
            }

            $normalizedViolations['values'][] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product' => $product]
            );
        }

        return $normalizedViolations;
    }

    private function handleProductDraft(ProductInterface $product, mixed $data): JsonResponse
    {
        try {
            $this->updateDraft($product, $data);
        } catch (TwoWayAssociationWithTheSameProductException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage(), 'global' => true],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $violations = $this->validator->validate($product);
        $violations->addAll($this->localizedConverter->getViolations());

        if (0 === $violations->count()) {
            $this->productSaver->save($product);

            $normalizedProduct = $this->normalizer->normalize(
                $product,
                'internal_api',
                $this->getNormalizationContext()
            );

            return new JsonResponse($normalizedProduct);
        }

        $normalizedViolations = $this->normalizeViolations($violations, $product);

        return new JsonResponse($normalizedViolations, 400);
    }
}
