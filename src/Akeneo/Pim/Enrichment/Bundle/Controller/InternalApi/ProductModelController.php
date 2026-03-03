<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelHandler;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\EntityWithValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelController
{
    private const PRODUCT_MODELS_LIMIT = 20;

    private NormalizerInterface $normalizer;
    private UserContext $userContext;
    private ObjectFilterInterface $objectFilter;
    private ProductModelRepositoryInterface $productModelRepository;
    private AttributeConverterInterface $localizedConverter;
    private EntityWithValuesFilter $emptyValuesFilter;
    private ConverterInterface $productValueConverter;
    private ObjectUpdaterInterface $productModelUpdater;
    private ValidatorInterface $productModelValidator;
    private SaverInterface $productModelSaver;
    private NormalizerInterface $constraintViolationNormalizer;
    private NormalizerInterface $entityWithFamilyVariantNormalizer;
    private SimpleFactoryInterface $productModelFactory;
    private NormalizerInterface $violationNormalizer;
    private FamilyVariantRepositoryInterface $familyVariantRepository;
    private AttributeFilterInterface $productModelAttributeFilter;
    private Client $productAndProductModelClient;
    private CollectionFilterInterface $productEditDataFilter;
    private RemoveProductModelHandler $removeProductModelHandler;
    private ValidatorInterface $validator;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        NormalizerInterface $normalizer,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        AttributeConverterInterface $localizedConverter,
        EntityWithValuesFilter $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        ObjectUpdaterInterface $productModelUpdater,
        ValidatorInterface $productModelValidator,
        SaverInterface $productModelSaver,
        NormalizerInterface $constraintViolationNormalizer,
        NormalizerInterface $entityWithFamilyVariantNormalizer,
        SimpleFactoryInterface $productModelFactory,
        NormalizerInterface $violationNormalizer,
        FamilyVariantRepositoryInterface $familyVariantRepository,
        AttributeFilterInterface $productModelAttributeFilter,
        Client $productAndProductModelClient,
        CollectionFilterInterface $productEditDataFilter,
        RemoveProductModelHandler $removeProductModelHandler,
        ValidatorInterface $validator
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->normalizer = $normalizer;
        $this->userContext = $userContext;
        $this->objectFilter = $objectFilter;
        $this->localizedConverter = $localizedConverter;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->productValueConverter = $productValueConverter;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelValidator = $productModelValidator;
        $this->productModelSaver = $productModelSaver;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->entityWithFamilyVariantNormalizer = $entityWithFamilyVariantNormalizer;
        $this->productModelFactory = $productModelFactory;
        $this->violationNormalizer = $violationNormalizer;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->productEditDataFilter = $productEditDataFilter;
        $this->removeProductModelHandler = $removeProductModelHandler;
        $this->validator = $validator;
    }

    /**
     * @param int $id Product model id
     *
     * @throws NotFoundHttpException If product model is not found or the user cannot see it
     *
     * @return JsonResponse
     */
    public function getAction(int $id): JsonResponse
    {
        $productModel = $this->findProductModelOr404($id);
        $normalizedProductModel = $this->normalizeProductModel($productModel);

        return new JsonResponse($normalizedProductModel);
    }

    /**
     * @param string $identifier
     *
     * @throws NotFoundHttpException If product model is not found or the user cannot see it
     *
     * @return JsonResponse
     */
    public function getByCodeAction(string $identifier): JsonResponse
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($identifier);
        $cantView = $this->objectFilter->filterObject($productModel, 'pim.internal_api.product.view');

        if (null === $productModel || true === $cantView) {
            throw new NotFoundHttpException(
                sprintf('Product model with identifier "%s" could not be found.', $identifier)
            );
        }

        $normalizedProductModel = $this->normalizeProductModel($productModel);

        return new JsonResponse($normalizedProductModel);
    }

    /**
     * Returns a set of product models from identifiers parameter
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request): JsonResponse
    {
        $productModelIdentifiers = explode(',', $request->get('identifiers'));
        $productModels = $this->productModelRepository->findByIdentifiers($productModelIdentifiers);

        $normalizedProductModels = array_map(function ($productModel) {
            return $this->normalizeProductModel($productModel);
        }, $productModels);

        return new JsonResponse($normalizedProductModels);
    }

    /**
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_product_model_create")
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productModel = $this->productModelFactory->create();
        $content = json_decode($request->getContent(), true);

        $this->productModelUpdater->update($productModel, $content);

        $violations = $this->productModelValidator->validate($productModel);

        if (count($violations) > 0) {
            $normalizedViolations = $this->normalizeCreateViolations($violations, $productModel);

            return new JsonResponse($normalizedViolations, 400);
        }

        $this->productModelSaver->save($productModel);
        $normalizedProductModel = $this->normalizeProductModel($productModel);

        return new JsonResponse($normalizedProductModel);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_product_model_edit_attributes")
     *
     * @return Response
     */
    public function postAction(Request $request, int $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productModel = $this->findProductModelOr404($id);
        $data = json_decode($request->getContent(), true);
        $data = $this->productEditDataFilter->filterCollection($data, null, ['product' => $productModel]);

        try {
            $this->updateProductModel($productModel, $data);
        } catch (TwoWayAssociationWithTheSameProductException $e) {
            return new JsonResponse(
                [
                    'message' => $e->getMessage(),
                    'global' => true,
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $violations = $this->productModelValidator->validate($productModel);
        $violations->addAll($this->localizedConverter->getViolations());

        if (0 === $violations->count()) {
            $this->productModelSaver->save($productModel);
            $normalizedProductModel = $this->normalizeProductModel($productModel);

            return new JsonResponse($normalizedProductModel);
        }

        $normalizedViolations = $this->normalizeEditViolations($violations, $productModel);

        return new JsonResponse($normalizedViolations, 400);
    }

    /**
     * Return direct children (products or product models) of the parent's given id
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function childrenAction(Request $request): JsonResponse
    {
        $parent = $this->findProductModelOr404($request->get('id'));
        if ($parent->isRoot() && 2 === $parent->getFamilyVariant()->getNumberOfLevel()) {
            $children = $parent->getProductModels();
        } else {
            $children = $parent->getProducts();
        }

        $localeCode = $request->get('locale', $this->userContext->getCurrentLocaleCode());
        $channelCode = $request->get('scope', $this->userContext->getUserChannelCode());

        $normalizedChildren = [];
        foreach ($children as $child) {
            if (!$child instanceof ProductModelInterface && !$child instanceof ProductInterface) {
                throw new \LogicException(sprintf(
                    'Child of a product model must be of class "%s" or "%s", "%s" received.',
                    ProductModelInterface::class,
                    ProductInterface::class,
                    get_class($child)
                ));
            }

            $normalizedChildren[] = $this->entityWithFamilyVariantNormalizer->normalize(
                $child,
                'internal_api',
                [
                    'locale' => $localeCode,
                    'channel' => $channelCode,
                ]
            );
        }

        return new JsonResponse($normalizedChildren);
    }

    /**
     * Returns the last level of product models belonging to a Family Variant with a given search code
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchLastLevelProductModelByCode(Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        $options = $request->query->get('options');
        $familyVariantCode = $options['family_variant'];
        $page = intval($options['page']) - 1;
        $familyVariant = $this->getFamilyVariant($familyVariantCode);

        $productModels = $this->productModelRepository->searchLastLevelByCode(
            $familyVariant,
            $search,
            self::PRODUCT_MODELS_LIMIT,
            $page
        );

        $normalizedProductModels = $this->buildNormalizedProductModels($productModels);

        return new JsonResponse($normalizedProductModels);
    }

    /**
     * Returns all the product models (sub and root) of a family variant
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listFamilyVariantProductModels(Request $request)
    {
        $search = trim($request->query->get('search'));
        $options = $request->query->get('options');
        $familyVariant = $this->getFamilyVariant($options['family_variant']);

        $productModels = $this->productModelRepository->findProductModelsForFamilyVariant(
            $familyVariant,
            $search,
            (int) ($options['limit'] ?? self::PRODUCT_MODELS_LIMIT),
            (int) ($options['page'] ?? 1)
        );
        $normalizedProductModels = $this->buildNormalizedProductModels($productModels);

        return new JsonResponse($normalizedProductModels);
    }

    /**
     * @AclAncestor("pim_enrich_product_model_remove")
     */
    public function removeAction(Request $request, int $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productModel = $this->findProductModelOr404($id);
        $command = new RemoveProductModelCommand($productModel->getCode());
        $violations = $this->validator->validate($command);
        if (0 < \count($violations)) {
            // Currently the UI expects only one error message in order to display it as a flash message.
            $firstViolation = $violations[0];
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $firstViolation->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ($this->removeProductModelHandler)($command);
        $this->productAndProductModelClient->refreshIndex();

        return new JsonResponse();
    }

    /**
     * Returns the family variant object from a family variant code
     *
     * @param string $familyVariantCode
     *
     * @throws \InvalidArgumentException
     *
     * @return FamilyVariantInterface
     */
    private function getFamilyVariant(string $familyVariantCode): FamilyVariantInterface
    {
        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariantCode);
        if (null === $familyVariant) {
            throw new \InvalidArgumentException(sprintf('Unknown family variant code "%s"', $familyVariantCode));
        }

        return $familyVariant;
    }

    /**
     * Returns an array of normalized product models from an array of product model objects
     *
     * @param array $productModels
     *
     * @return array
     */
    private function buildNormalizedProductModels(array $productModels): array
    {
        $normalizedProductModels = [];
        foreach ($productModels as $productModel) {
            $normalizedProductModels[$productModel->getCode()] = $this->normalizeProductModel(
                $productModel
            );
        }

        return $normalizedProductModels;
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function normalizeProductModel(ProductModelInterface $productModel): array
    {
        $normalizationContext = $this->userContext->toArray() + ['filter_types' => []];

        return $this->normalizer->normalize(
            $productModel,
            'internal_api',
            $normalizationContext
        );
    }

    /**
     * Updates product with the provided request data
     *
     * @param ProductModelInterface $productModel
     * @param array                 $data
     */
    private function updateProductModel(ProductModelInterface $productModel, array $data): void
    {
        unset($data['parent']);
        $values = $this->productValueConverter->convert($data['values']);

        $values = $this->localizedConverter->convertToDefaultFormats($values, [
            'locale' => $this->userContext->getUiLocale()->getCode()
        ]);

        $dataFiltered = $this->emptyValuesFilter->filter($productModel, ['values' => $values]);

        if (!empty($dataFiltered)) {
            $data = array_replace($data, $dataFiltered);
        } else {
            $data['values'] = [];
        }

        if (!$productModel->isRoot()) {
            $data = $this->productModelAttributeFilter->filter($data);
        }

        $this->productModelUpdater->update($productModel, $data);
    }

    /**
     * Find a product model by its id or throw a 404
     *
     * @param string $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductModelInterface
     */
    protected function findProductModelOr404($id): ProductModelInterface
    {
        $productModel = $this->productModelRepository->find($id);
        $productModel = $this->objectFilter->filterObject($productModel, 'pim.internal_api.product.view') ? null : $productModel;

        if (null === $productModel) {
            throw new NotFoundHttpException(
                sprintf('ProductModel with id %s could not be found.', $id)
            );
        }

        return $productModel;
    }

    protected function normalizeCreateViolations(ConstraintViolationListInterface $violations, ProductModelInterface $productModel): array
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

            $normalizedViolations['values'][] = $this->violationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product_model' => $productModel]
            );
        }

        return $normalizedViolations;
    }

    protected function normalizeEditViolations(ConstraintViolationListInterface $violations, ProductModelInterface $productModel): array
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
                ['productModel' => $productModel]
            );
        }

        return $normalizedViolations;
    }
}
