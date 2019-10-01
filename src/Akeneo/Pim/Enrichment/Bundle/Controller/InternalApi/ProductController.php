<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
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
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var CursorableRepositoryInterface */
    protected $cursorableRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserContext */
    protected $userContext;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /** @var CollectionFilterInterface */
    protected $productEditDataFilter;

    /** @var RemoverInterface */
    protected $productRemover;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var FilterInterface */
    protected $emptyValuesFilter;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /** @var ProductBuilderInterface */
    protected $variantProductBuilder;

    /** @var AttributeFilterInterface */
    protected $productAttributeFilter;

    /** @var Client */
    private $productAndProductModelClient;

    /**
     * @param ProductRepositoryInterface    $productRepository
     * @param CursorableRepositoryInterface $cursorableRepository
     * @param AttributeRepositoryInterface  $attributeRepository
     * @param ObjectUpdaterInterface        $productUpdater
     * @param SaverInterface                $productSaver
     * @param NormalizerInterface           $normalizer
     * @param ValidatorInterface            $validator
     * @param UserContext                   $userContext
     * @param ObjectFilterInterface         $objectFilter
     * @param CollectionFilterInterface     $productEditDataFilter
     * @param RemoverInterface              $productRemover
     * @param ProductBuilderInterface       $productBuilder
     * @param AttributeConverterInterface   $localizedConverter
     * @param FilterInterface               $emptyValuesFilter
     * @param ConverterInterface            $productValueConverter
     * @param NormalizerInterface           $constraintViolationNormalizer
     * @param ProductBuilderInterface       $variantProductBuilder
     * @param AttributeFilterInterface      $productAttributeFilter
     * @param Client                        $productAndProductModelClient
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CursorableRepositoryInterface $cursorableRepository,
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        CollectionFilterInterface $productEditDataFilter,
        RemoverInterface $productRemover,
        ProductBuilderInterface $productBuilder,
        AttributeConverterInterface $localizedConverter,
        FilterInterface $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        NormalizerInterface $constraintViolationNormalizer,
        ProductBuilderInterface $variantProductBuilder,
        AttributeFilterInterface $productAttributeFilter,
        Client $productAndProductModelClient
    ) {
        $this->productRepository = $productRepository;
        $this->cursorableRepository = $cursorableRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->userContext = $userContext;
        $this->objectFilter = $objectFilter;
        $this->productEditDataFilter = $productEditDataFilter;
        $this->productRemover = $productRemover;
        $this->productBuilder = $productBuilder;
        $this->localizedConverter = $localizedConverter;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->productValueConverter = $productValueConverter;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->variantProductBuilder = $variantProductBuilder;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->productAndProductModelClient = $productAndProductModelClient;
    }

    /**
     * Returns a set of products from identifiers parameter
     *
     * @param Request $request
     *
     * @return JsonResponse
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
    public function getAction($id)
    {
        $product = $this->findProductOr404($id);

        $normalizedProduct = $this->normalizer->normalize(
            $product,
            'internal_api',
            $this->getNormalizationContext()
        );

        return new JsonResponse($normalizedProduct);
    }

    /**
     * @param Request $request
     *
     * @return Response
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

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product' => $product]
            );
        }

        return new JsonResponse(['values' => $normalizedViolations], 400);
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     *
     * @return Response
     */
    public function postAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
        if ($this->objectFilter->filterObject($product, 'pim.internal_api.product.edit')) {
            throw new AccessDeniedHttpException();
        }
        $data = json_decode($request->getContent(), true);
        try {
            $data = $this->productEditDataFilter->filterCollection($data, null, ['product' => $product]);
        } catch (ObjectNotFoundException $e) {
            throw new BadRequestHttpException();
        }
        $this->updateProduct($product, $data);

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

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product' => $product]
            );
        }

        return new JsonResponse(['values' => $normalizedViolations], 400);
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
}
