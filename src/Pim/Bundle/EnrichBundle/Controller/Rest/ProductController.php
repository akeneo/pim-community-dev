<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /** @var ProductFilterInterface */
    protected $emptyValuesFilter;

    /**
     * @param ProductRepositoryInterface   $productRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ObjectUpdaterInterface       $productUpdater
     * @param SaverInterface               $productSaver
     * @param NormalizerInterface          $normalizer
     * @param ValidatorInterface           $validator
     * @param UserContext                  $userContext
     * @param ObjectFilterInterface        $objectFilter
     * @param CollectionFilterInterface    $productEditDataFilter
     * @param RemoverInterface             $productRemover
     * @param ProductBuilderInterface      $productBuilder
     * @param AttributeConverterInterface  $localizedConverter
     * @param ProductFilterInterface       $emptyValuesFilter
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
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
        ProductFilterInterface $emptyValuesFilter
    ) {
        $this->productRepository = $productRepository;
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
    }

    /**
     * Edit product
     *
     * @param int $id
     *
     * @Template("PimEnrichBundle:Product:edit.html.twig")
     * @AclAncestor("pim_enrich_product_index")
     *
     * @return array
     */
    public function editAction($id)
    {
        return [
            'productId' => $id
        ];
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
        $this->productBuilder->addMissingAssociations($product);

        $normalizationContext = $this->userContext->toArray() + [
            'filter_types'               => ['pim.internal_api.product_value.view'],
            'disable_grouping_separator' => true
        ];

        return new JsonResponse($this->normalizer->normalize(
            $product,
            'internal_api',
            $normalizationContext
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->productBuilder->createProduct(
            $request->request->get('identifier'),
            $request->request->get('family', null)
        );

        $violations = $this->validator->validate($product);
        if (0 === $violations->count()) {
            $this->productSaver->save($product);

            $normalizationContext = $this->userContext->toArray() + [
                'filter_types'               => ['pim.internal_api.product_value.view'],
                'disable_grouping_separator' => true
            ];

            return new JsonResponse($this->normalizer->normalize(
                $product,
                'internal_api',
                $normalizationContext
            ));
        }

        $errors = [
            'values' => $this->normalizer->normalize($violations, 'internal_api', ['product' => $product])
        ];

        return new JsonResponse($errors, 400);
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     *
     * @return JsonResponse|RedirectResponse
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

            $normalizationContext = $this->userContext->toArray() + [
                'filter_types'               => ['pim.internal_api.product_value.view'],
                'disable_grouping_separator' => true
            ];

            return new JsonResponse($this->normalizer->normalize(
                $product,
                'internal_api',
                $normalizationContext
            ));
        }

        $errors = [
            'values' => $this->normalizer->normalize($violations, 'internal_api', ['product' => $product])
        ];

        return new JsonResponse($errors, 400);
    }

    /**
     * Remove product
     *
     * @param int $id
     *
     * @AclAncestor("pim_enrich_product_remove")
     *
     * @return JsonResponse|RedirectResponse
     */
    public function removeAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
        $this->productRemover->remove($product);

        return new JsonResponse();
    }

    /**
     * Remove an optional attribute from a product
     *
     * @param int $id          The product id
     * @param int $attributeId The attribute id
     *
     * @AclAncestor("pim_enrich_product_remove_attribute")
     *
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     * @throws BadRequestHttpException   If the attribute is not removable
     *
     * @return JsonResponse|RedirectResponse
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

        $this->productBuilder->removeAttributeFromProduct($product, $attribute);
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
        $product = $this->productRepository->findOneByWithValues($id);
        $product = $this->objectFilter->filterObject($product, 'pim.internal_api.product.view') ? null : $product;

        if (!$product) {
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
        $values = $this->localizedConverter->convertToDefaultFormats($data['values'], [
            'locale' => $this->userContext->getUiLocale()->getCode()
        ]);

        $values = $this->emptyValuesFilter->filter($product, $values);

        unset($data['values']);
        $data = array_replace($data, $values);

        $this->productUpdater->update($product, $data);
    }
}
