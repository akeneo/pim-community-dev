<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /** @var ConverterInterface */
    protected $productValueConverter;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var ObjectManager */
    protected $productManager;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var NormalizerInterface */
    protected $completenessCollectionNormalizer;

    /** @var string */
    protected $storageDriver;

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
     * @param ConverterInterface           $productValueConverter
     * @param CompletenessManager          $completenessManager
     * @param ObjectManager                $productManager
     * @param ChannelRepositoryInterface   $channelRepository
     * @param CollectionFilterInterface    $collectionFilter
     * @param NormalizerInterface          $completenessCollectionNormalizer
     * @param string                       $storageDriver
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
        ProductFilterInterface $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        CompletenessManager $completenessManager,
        ObjectManager $productManager,
        ChannelRepositoryInterface $channelRepository,
        CollectionFilterInterface $collectionFilter,
        NormalizerInterface $completenessCollectionNormalizer,
        $storageDriver
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
        $this->productValueConverter = $productValueConverter;
        $this->completenessManager = $completenessManager;
        $this->productManager = $productManager;
        $this->channelRepository = $channelRepository;
        $this->collectionFilter = $collectionFilter;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
        $this->storageDriver = $storageDriver;
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

        $normalizedProduct = $this->normalizer->normalize(
            $product,
            'internal_api',
            $normalizationContext
        );
        $normalizedProduct['meta']['completenesses'] = $this->getNormalizedCompletenesses($product);

        return new JsonResponse($normalizedProduct);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
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
     * @return JsonResponse
     */
    public function postAction(Request $request, $id)
    {
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

            $normalizedProduct = $this->normalizer->normalize(
                $product,
                'internal_api',
                $normalizationContext
            );
            $normalizedProduct['meta']['completenesses'] = $this->getNormalizedCompletenesses($product);

            return new JsonResponse($normalizedProduct);
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
     * @return JsonResponse
     */
    public function removeAction($id)
    {
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
     * @return JsonResponse
     */
    public function removeAttributeAction($id, $attributeId)
    {
        $product = $this->findProductOr404($id);
        if ($this->objectFilter->filterObject($product, 'pim.internal_api.product.edit')) {
            throw new AccessDeniedHttpException();
        }

        $attribute = $this->findAttributeOr404($attributeId);

        if (!$product->isAttributeRemovable($attribute)) {
            throw new BadRequestHttpException();
        }

        foreach ($product->getValues() as $value) {
            if ($attribute === $value->getAttribute()) {
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


        $this->productUpdater->update($product, $data);
    }

    /**
     * Get Product Completeness and normalize it
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getNormalizedCompletenesses(ProductInterface $product)
    {
        $this->completenessManager->generateMissingForProduct($product);
        // Product have to be refreshed to have the completeness values generated by generateMissingForProduct()
        // (on ORM, completeness is not calculated the same way and product doesn't need to be refreshed)
        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $this->storageDriver) {
            $this->productManager->refresh($product);
        }

        $channels = $this->channelRepository->getFullChannels();
        $locales = $this->userContext->getUserLocales();

        $filteredLocales = $this->collectionFilter->filterCollection($locales, 'pim.internal_api.locale.view');
        $completenesses = $this->completenessManager->getProductCompleteness($product, $channels, $filteredLocales);

        return $this->completenessCollectionNormalizer->normalize($completenesses, 'internal_api');
    }
}
