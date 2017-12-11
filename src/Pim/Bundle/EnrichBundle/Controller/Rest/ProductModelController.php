<?php
declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Comparator\Filter\EntityWithValuesFilter;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelController
{
    private const PRODUCT_MODELS_LIMIT = 20;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var UserContext */
    private $userContext;

    /** @var ObjectFilterInterface */
    private $objectFilter;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var AttributeConverterInterface */
    private $localizedConverter;

    /** @var EntityWithValuesFilter */
    private $emptyValuesFilter;

    /** @var ConverterInterface */
    private $productValueConverter;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var RemoverInterface */
    private $productModelRemover;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var NormalizerInterface */
    private $constraintViolationNormalizer;

    /** @var EntityWithFamilyVariantNormalizer */
    private $entityWithFamilyVariantNormalizer;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var NormalizerInterface */
    private $violationNormalizer;

    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /**
     * @param ProductModelRepositoryInterface   $productModelRepository
     * @param NormalizerInterface               $normalizer
     * @param UserContext                       $userContext
     * @param ObjectFilterInterface             $objectFilter
     * @param AttributeConverterInterface       $localizedConverter
     * @param EntityWithValuesFilter            $emptyValuesFilter
     * @param ConverterInterface                $productValueConverter
     * @param ObjectUpdaterInterface            $productModelUpdater
     * @param ValidatorInterface                $validator
     * @param SaverInterface                    $productModelSaver
     * @param NormalizerInterface               $constraintViolationNormalizer
     * @param EntityWithFamilyVariantNormalizer $entityWithFamilyVariantNormalizer
     * @param SimpleFactoryInterface            $productModelFactory
     * @param NormalizerInterface               $violationNormalizer
     * @param FamilyVariantRepositoryInterface  $familyVariantRepository
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        NormalizerInterface $normalizer,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        AttributeConverterInterface $localizedConverter,
        EntityWithValuesFilter $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        ObjectUpdaterInterface $productModelUpdater,
        RemoverInterface $productModelRemover,
        ValidatorInterface $validator,
        SaverInterface $productModelSaver,
        NormalizerInterface $constraintViolationNormalizer,
        EntityWithFamilyVariantNormalizer $entityWithFamilyVariantNormalizer,
        SimpleFactoryInterface $productModelFactory,
        NormalizerInterface $violationNormalizer,
        FamilyVariantRepositoryInterface $familyVariantRepository
    ) {
        $this->productModelRepository        = $productModelRepository;
        $this->normalizer                    = $normalizer;
        $this->userContext                   = $userContext;
        $this->objectFilter                  = $objectFilter;
        $this->localizedConverter            = $localizedConverter;
        $this->emptyValuesFilter             = $emptyValuesFilter;
        $this->productValueConverter         = $productValueConverter;
        $this->productModelUpdater           = $productModelUpdater;
        $this->productModelRemover           = $productModelRemover;
        $this->validator                     = $validator;
        $this->productModelSaver             = $productModelSaver;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->entityWithFamilyVariantNormalizer = $entityWithFamilyVariantNormalizer;
        $this->productModelFactory           = $productModelFactory;
        $this->violationNormalizer = $violationNormalizer;
        $this->familyVariantRepository = $familyVariantRepository;
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
        $productModel = $this->productModelRepository->find($id);
        $cantView = $this->objectFilter->filterObject($productModel, 'pim.internal_api.product.view');

        if (null === $productModel || true === $cantView) {
            throw new NotFoundHttpException(
                sprintf('Product model with id %s could not be found.', $id)
            );
        }

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
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_product_model_create")
     *
     * @return JsonResponse
     */
    public function createAction(Request $request): JsonResponse
    {
        $productModel = $this->productModelFactory->create();
        $content = json_decode($request->getContent(), true);

        $this->productModelUpdater->update($productModel, $content);

        $violations = $this->validator->validate($productModel);

        if (count($violations) > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->violationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['product_model' => $productModel]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->productModelSaver->save($productModel);

        $normalizationContext = $this->userContext->toArray() + ['disable_grouping_separator' => true];
        $normalizedProduct = $this->normalizer->normalize(
            $productModel,
            'internal_api',
            $normalizationContext
        );

        return new JsonResponse($normalizedProduct);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_product_model_edit_attributes")
     *
     * @return JsonResponse
     */
    public function postAction(Request $request, int $id): JsonResponse
    {
        $productModel = $this->productModelRepository->find($id);
        $data = json_decode($request->getContent(), true);

        $this->updateProductModel($productModel, $data);

        $violations = $this->validator->validate($productModel);
        $violations->addAll($this->localizedConverter->getViolations());

        if (0 === $violations->count()) {
            $this->productModelSaver->save($productModel);

            $normalizationContext = $this->userContext->toArray() + ['disable_grouping_separator' => true];
            $normalizedProduct = $this->normalizer->normalize(
                $productModel,
                'internal_api',
                $normalizationContext
            );

            return new JsonResponse($normalizedProduct);
        }

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['productModel' => $productModel]
            );
        }

        return new JsonResponse(['values' => $normalizedViolations], 400);
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
        $parentId = $request->query->get('id');
        $parent = $this->productModelRepository->find($parentId);
        if (null === $parent) {
            throw new NotFoundHttpException(sprintf('ProductModel with id "%s" not found', $parentId));
        }

        $children = $this->productModelRepository->findChildrenProductModels($parent);
        if (empty($children)) {
            $children = $this->productModelRepository->findChildrenProducts($parent);
        }

        $normalizedChildren = [];
        foreach ($children as $child) {
            if (!$child instanceof ProductModelInterface && !$child instanceof VariantProductInterface) {
                throw new \LogicException(sprintf(
                    'Child of a product model must be of class "%s" or "%s", "%s" received.',
                    ProductModelInterface::class,
                    VariantProductInterface::class,
                    get_class($child)
                ));
            }

            $normalizedChildren[] = $this->entityWithFamilyVariantNormalizer->normalize(
                $child,
                'internal_api',
                ['locale' => $this->userContext->getCurrentLocaleCode()]
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
        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariantCode);
        if (null === $familyVariant) {
            throw new \InvalidArgumentException(sprintf('Unknown family variant code "%s"', $familyVariantCode));
        }

        $productModels = $this->productModelRepository->searchLastLevelByCode(
            $familyVariant,
            $search,
            self::PRODUCT_MODELS_LIMIT,
            $page
        );

        $normalizedProductModels = [];
        foreach ($productModels as $productModel) {
            $normalizedProductModels[$productModel->getCode()] = $this->normalizeProductModel(
                $productModel
            );
        }

        return new JsonResponse($normalizedProductModels);
    }

    /**
     * Remove product model
     *
     * @param int $id
     *
     * @AclAncestor("pim_enrich_product_model_remove")
     *
     * @return JsonResponse
     */
    public function removeAction($id): JsonResponse
    {
        $productModel = $this->findProductModelOr404($id);
        $this->productModelRemover->remove($productModel);

        return new JsonResponse();
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function normalizeProductModel(ProductModelInterface $productModel): array
    {
        $normalizationContext = $this->userContext->toArray() + [
                'filter_types'               => ['pim.internal_api.product_value.view'],
                'disable_grouping_separator' => true
            ];

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
}
