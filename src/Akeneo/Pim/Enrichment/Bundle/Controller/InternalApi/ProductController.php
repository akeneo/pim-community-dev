<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Automation\IdentifierGenerator\API\Presenter\UnableToSetIdentifierExceptionPresenterInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\API\Subscriber\UnableToSetIdentifiersSubscriberInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected AttributeRepositoryInterface $attributeRepository,
        protected ObjectUpdaterInterface $productUpdater,
        protected SaverInterface $productSaver,
        protected NormalizerInterface $normalizer,
        protected ValidatorInterface $validator,
        protected UserContext $userContext,
        protected ObjectFilterInterface $objectFilter,
        protected CollectionFilterInterface $productEditDataFilter,
        protected RemoverInterface $productRemover,
        protected ProductBuilderInterface $productBuilder,
        protected AttributeConverterInterface $localizedConverter,
        protected FilterInterface $emptyValuesFilter,
        protected ConverterInterface $productValueConverter,
        protected NormalizerInterface $constraintViolationNormalizer,
        protected ProductBuilderInterface $variantProductBuilder,
        protected AttributeFilterInterface $productAttributeFilter,
        private Client $productAndProductModelClient,
        private MessageBusInterface $commandMessageBus,
        private FindIdentifier $findIdentifier,
        private UnableToSetIdentifiersSubscriberInterface $unableToSetIdentifiersSubscriber,
        private UnableToSetIdentifierExceptionPresenterInterface $unableToSetIdentifierExceptionPresenter,
    ) {
    }

    /**
     * Returns a set of products from identifiers parameter
     */
    public function indexAction(Request $request): JsonResponse
    {
        $productUuids = explode(',', $request->get('uuids'));

        $products = $this->productRepository->getItemsFromUuids($productUuids);

        $normalizedProducts = $this->normalizer->normalize(
            $products,
            'internal_api',
            $this->getNormalizationContext()
        );

        return new JsonResponse($normalizedProducts);
    }

    public function getAction(Request $request, string $uuid): JsonResponse
    {
        $product = $this->findProductOr404($uuid);

        $context = $this->getNormalizationContext();
        $context['catalogLocale'] = $request->get('catalogLocale');
        $context['catalogScope'] = $request->get('catalogScope');

        $normalizedProduct = $this->normalizer->normalize(
            $product,
            'internal_api',
            $context
        );

        return new JsonResponse($normalizedProduct);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @AClAncestor("pim_enrich_product_create")
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['parent'])) {
            $product = $this->variantProductBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );

            if (isset($data['values'])) {
                $this->createProduct($product, $data);
            }
        } else {
            $product = $this->productBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );
        }

        $violations = $this->validator->validate($product);

        if (0 === $violations->count()) {
            $this->productSaver->save($product);

            $normalizedProduct = $this->normalizer->normalize(
                $product,
                'internal_api',
                $this->getNormalizationContext()
            );

            $events = $this->unableToSetIdentifiersSubscriber->getEvents();
            if (\count($events) > 0) {
                $normalizedProduct['meta']['identifier_generator_warnings'] = \array_merge(
                    ...\array_map(
                        fn ($event): array => $this->unableToSetIdentifierExceptionPresenter->present($event->getException()),
                        $events
                    )
                );
            }

            return new JsonResponse($normalizedProduct);
        }

        $normalizedViolations = $this->normalizeViolations($violations, $product);

        return new JsonResponse($normalizedViolations, 400);
    }

    /**
     * Remove product
     *
     * @param Request $request
     * @param int     $uuid
     *
     * @AclAncestor("pim_enrich_product_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, $uuid)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($uuid);
        $this->productRemover->remove($product);

        $this->productAndProductModelClient->refreshIndex();

        return new JsonResponse();
    }

    /**
     * Remove an optional attribute from a product
     *
     * @param Request $request
     * @param string  $uuid
     * @param string  $attributeId
     * @return JsonResponse|RedirectResponse
     *
     * @AclAncestor("pim_enrich_product_remove_attribute")
     *
     * @return Response
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     * @throws BadRequestHttpException   If the attribute is not removable
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     */
    public function removeAttributeAction(Request $request, $uuid, $attributeId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($uuid);
        if ($this->objectFilter->filterObject($product, 'pim.internal_api.product.edit')) {
            throw new AccessDeniedHttpException();
        }

        $attribute = $this->findAttributeOr404($attributeId);

        if (!$product->isAttributeRemovable($attribute)) {
            throw new BadRequestHttpException();
        }

        foreach ($product->getValues() as $value) {
            if ($attribute->getCode() === $value->getAttributeCode()) {
                $product->removeValue($value);
            }
        }
        $this->productSaver->save($product);

        return new JsonResponse();
    }

    /**
     * Converts a variant product into a simple product
     *
     * @AclAncestor("pim_enrich_product_convert_variant_to_simple")
     */
    public function convertToSimpleProductAction(Request $request, string $uuid): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($uuid);

        try {
            $userId = $this->userContext->getUser()?->getId();
            $command = UpsertProductCommand::createWithUuid(
                $userId,
                ProductUuid::fromUuid($product->getUuid()),
                [new ConvertToSimpleProduct()]
            );
            $this->commandMessageBus->dispatch($command);
        } catch (ViolationsException $e) {
            $hasPermissionException = \count(
                \array_filter(
                    $e->violations(),
                    fn (
                        ConstraintViolationInterface $violation
                    ): bool => $violation->getCode() === (string) ViolationCode::PERMISSION
                )
            ) > 0;
            if ($hasPermissionException) {
                throw new AccessDeniedHttpException();
            }
            $normalizedViolations = $this->normalizeViolations($e->violations(), $product);

            return new JsonResponse($normalizedViolations, 400);
        }

        return new JsonResponse();
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param string $uuid the product uuid
     *
     * @return ProductInterface
     * @throws NotFoundHttpException
     */
    protected function findProductOr404(string $uuid): ProductInterface
    {
        $product = $this->productRepository->find($uuid);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with uuid %s could not be found.', (string) $uuid)
            );
        }

        return $product;
    }

    protected function findProductIdentifierOr404(string $uuid): string
    {
        $identifier = $this->findIdentifier->fromUuid($uuid);

        if (null === $identifier) {
            throw new NotFoundHttpException(
                sprintf('Product with uuid %s could not be found.', $uuid)
            );
        }

        return $identifier;
    }

    /**
     * Find an attribute by its id or return a 404 response
     *
     * @param int $id the attribute id
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        $attribute = $this->attributeRepository->find($id);

        if (!$attribute) {
            throw new NotFoundHttpException(
                sprintf('Attribute with id %d could not be found.', $id)
            );
        }

        return $attribute;
    }

    protected function createProduct(ProductInterface $product, array $data)
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
        if (!$product->isNew() && $product->isVariant()) {
            $data = $this->productAttributeFilter->filter($data);
        }

        $this->productUpdater->update($product, $data);
    }

    /**
     * Get the context used for product normalization
     *
     * @return array
     */
    protected function getNormalizationContext(): array
    {
        return $this->userContext->toArray() + ['filter_types' => []];
    }

    protected function normalizeViolations(ConstraintViolationListInterface $violations, ProductInterface $product): array
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
}
