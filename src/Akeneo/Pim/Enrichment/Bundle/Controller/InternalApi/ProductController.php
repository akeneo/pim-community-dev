<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\MessageBus;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
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
        protected CursorableRepositoryInterface $cursorableRepository,
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
        private MessageBus $messageBus,
        private FindIdentifier $findIdentifier
    ) {
    }

    /**
     * Returns a set of products from identifiers parameter
     */
    public function indexAction(Request $request): JsonResponse
    {
        $productIdentifiers = explode(',', $request->get('identifiers'));
        $products = $this->cursorableRepository->getItemsFromIdentifiers($productIdentifiers);

        $normalizedProducts = $this->normalizer->normalize(
            $products,
            'internal_api',
            $this->getNormalizationContext()
        );

        return new JsonResponse($normalizedProducts);
    }

    /**
     * @param string $id Product id
     *
     * @throws NotFoundHttpException If product is not found or the user cannot see it
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, string $id)
    {
        $product = $this->findProductOr404($id);

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
                $this->updateProduct($product, $data);
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

            return new JsonResponse($this->normalizer->normalize(
                $product,
                'internal_api',
                $this->getNormalizationContext()
            ));
        }

        $normalizedViolations = $this->normalizeViolations($violations, $product);

        return new JsonResponse($normalizedViolations, 400);
    }

    /**
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     */
    public function postAction(Request $request, string $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
        $data = json_decode($request->getContent(), true);
        try {
            $data = $this->productEditDataFilter->filterCollection($data, null, ['product' => $product]);
        } catch (ObjectNotFoundException $e) {
            throw new BadRequestHttpException();
        }
        try {
            $this->updateProduct($product, $data);
        } catch (TwoWayAssociationWithTheSameProductException $e) {
            return new JsonResponse(
                [
                    'message' => $e->getMessage(),
                    'global' => true],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

//        $violations = $this->validator->validate($product);
//        $violations->addAll($this->localizedConverter->getViolations());

//        if (0 === $violations->count()) {
//            $this->productSaver->save($product);
//
//            $normalizedProduct = $this->normalizer->normalize(
//                $product,
//                'internal_api',
//                $this->getNormalizationContext()
//            );
//
//            return new JsonResponse($normalizedProduct);
//        }
//
//        $normalizedViolations = $this->normalizeViolations($violations, $product);
//
//        return new JsonResponse($normalizedViolations, 400);
        return new JsonResponse();
    }

    /**
     * Remove product
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_product_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
        $this->productRemover->remove($product);

        $this->productAndProductModelClient->refreshIndex();

        return new JsonResponse();
    }

    /**
     * Remove an optional attribute from a product
     *
     * @param Request $request
     * @param string  $id
     * @param string  $attributeId
     * @return JsonResponse|RedirectResponse
     *
     * @AclAncestor("pim_enrich_product_remove_attribute")
     *
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     * @throws BadRequestHttpException   If the attribute is not removable
     *
     * @return Response
     */
    public function removeAttributeAction(Request $request, $id, $attributeId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
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
    public function convertToSimpleProductAction(Request $request, int $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productIdentifier = $this->findProductIdentifierOr404($id);

        try {
            $userId = $this->userContext->getUser()?->getId();
            $command = UpsertProductCommand::createFromCollection(
                $userId,
                $productIdentifier,
                [new ConvertToSimpleProduct()]
            );
            $this->messageBus->dispatch($command);
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
            $product = $this->findProductOr404($id);
            $normalizedViolations = $this->normalizeViolations($e->violations(), $product);

            return new JsonResponse($normalizedViolations, 400);
        }

        return new JsonResponse();
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
    protected function findProductOr404($id)
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', $id)
            );
        }

        return $product;
    }

    protected function findProductIdentifierOr404(int $id): string
    {
        $identifier = $this->findIdentifier->fromId($id);

        if (null === $identifier) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', $id)
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

    /**
     * Updates product with the provided request data
     *
     * @param ProductInterface $product
     * @param array            $data
     */
    protected function updateProduct(ProductInterface $product, array $data)
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

        $userIntents = $this->getUserIntentsFromData($data);
        $userId = $this->userContext->getUser()?->getId();
        $command = UpsertProductCommand::createFromCollection(
            $userId,
            $product->getIdentifier(),
            $userIntents
        );
        $this->messageBus->dispatch($command);
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

    /**
     * @param array $data
     * {
     *  "identifier":"coucou",
     *  "family":"webcams",
     *  "parent":null,
     *  "groups":[],
     *  "categories":[],
     *  "enabled":true,
     *  "values":{
         * "sku":[{"scope":null,"locale":null,"data":"coucou"}],
         * "name":[{"scope":null,"locale":null,"data":null}],
         * "description":[
             * {"scope":"ecommerce","locale":"de_DE","data":null},
             * {"scope":"ecommerce","locale":"en_US","data":null},
             * {"scope":"ecommerce","locale":"fr_FR","data":null},
         * ],
         * "release_date":[
             * {"scope":"ecommerce","locale":null,"data":null},
             * {"scope":"mobile","locale":null,"data":null},
             * {"scope":"print","locale":null,"data":null}
         * ],
         * "weight":[{"scope":null,"locale":null,"data":{"unit":null,"amount":null}}],
         * "power_requirements":[{"scope":null,"locale":null,"data":null}],
         * "total_megapixels":[{"scope":null,"locale":null,"data":null}],
         * "maximum_video_resolution":[{"scope":null,"locale":null,"data":null}],
         * "maximum_frame_rate":[{"scope":null,"locale":null,"data":null}],
         * "picture":[{"scope":null,"locale":null,
         * "data":{"filePath":null,"originalFilename":null}}],
         * "price":[{"scope":null,"locale":null,"data":[{"currency":"EUR","amount":null},{"currency":"USD","amount":null}]}]
     * },
     * "created":"2022-05-06T12:28:58+00:00",
     * "updated":"2022-05-06T12:28:58+00:00",
     * "associations":{
         * "PACK":{"groups":[],"products":[],"product_models":[]},
         * "SUBSTITUTION":{"groups":[],"products":[],"product_models":[]},
         * "UPSELL":{"groups":[],"products":[],"product_models":[]},
         * "X_SELL":{"groups":[],"products":[],"product_models":[]}
     * },
     * "quantified_associations":[],
     * "parent_associations":[]}
     * @return array
     */
    private function getUserIntentsFromData(array $data): array
    {
        $userIntents = [
            new SetFamily($data['family']),
            new SetCategories($data['categories']),
            new SetEnabled($data['enabled']),
        ];

        if (null !== $data['groups']) {
            $userIntents[] = new SetGroups($data['groups']);
        }
        if (null !== $data['parent']) {
            $userIntents[] = new ChangeParent($data['parent']);
        }

        $codes = \array_keys($data['values']);
        $attributes = $this->attributeRepository->getAttributeTypeByCodes($codes);
        $dataValues = $data['values'];

        try {
//currency & amount


        foreach ($attributes as $attributeCode => $attributeType) {
             $values = $dataValues[$attributeCode];
             foreach ($values as $value) {
                 $scope = $value['scope'];
                 $locale = $value['locale'];
                 $data = $value['data'];
                 if (
                     null === $data
                     || ($attributeType === AttributeTypes::METRIC && (null === $data['amount' || null === $data['unit']]))
                     // TODO: what to do with property$data['originalFilename']
                     || ($attributeType === AttributeTypes::IMAGE && null === $data['filePath'])
                     || ($attributeType === AttributeTypes::PRICE_COLLECTION && \count(\array_filter($data, fn($value) => $value !== null)) > 0)
                 ) {
                     $userIntents[] = new ClearValue($attributeCode, $scope, $locale);
                     continue;
                 }
                 $userIntents[] = match ($attributeType) {
                     AttributeTypes::BOOLEAN => new SetBooleanValue($attributeCode, $scope, $locale, $data),
                     AttributeTypes::DATE => new SetDateValue($attributeCode, $scope, $locale, new \DateTime($data)),
                     AttributeTypes::FILE => new SetFileValue($attributeCode, $scope, $locale, $data),
                     AttributeTypes::IDENTIFIER => new SetIdentifierValue($attributeCode, $data),
                     AttributeTypes::IMAGE => new SetImageValue($attributeCode, $scope, $locale, $data['filePath']),
                     AttributeTypes::METRIC => new SetMeasurementValue($attributeCode, $scope, $locale, $data['amount'], $data['unit']),
                     AttributeTypes::NUMBER => new SetNumberValue($attributeCode, $scope, $locale, $data),
                     AttributeTypes::OPTION_MULTI_SELECT => new SetMultiSelectValue($attributeCode, $scope, $locale, $data),
                     AttributeTypes::PRICE_COLLECTION => new SetPriceCollectionValue($attributeCode, $scope, $locale, \array_filter($data, fn($value) => $value !== null)),
                     AttributeTypes::TEXTAREA => new SetTextareaValue($attributeCode, $scope, $locale, $data),
                     // TODO: use SetTableValue
                     AttributeTypes::TEXT, AttributeTypes::TABLE => new SetTextValue($attributeCode, $scope, $locale, $data),
                     AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT => new SetSimpleReferenceEntityValue($attributeCode, $scope, $locale, $data),
                     AttributeTypes::REFERENCE_ENTITY_COLLECTION => new SetMultiReferenceEntityValue($attributeCode, $scope, $locale, $data),
                     AttributeTypes::ASSET_COLLECTION => new SetAssetValue($attributeCode, $scope, $locale, $data),
                 };
             }
        }
        } catch(\Exception $e) {
            var_dump($e);
        }

        return $userIntents;
    }
}
