<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var PropertySetterInterface */
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

    /**
     * @param ProductRepositoryInterface   $productRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param PropertySetterInterface      $productUpdater
     * @param SaverInterface               $productSaver
     * @param NormalizerInterface          $normalizer
     * @param ValidatorInterface           $validator
     * @param UserContext                  $userContext
     * @param ObjectFilterInterface        $objectFilter
     * @param CollectionFilterInterface    $productEditDataFilter
     * @param RemoverInterface             $productRemover
     * @param ProductBuilderInterface      $productBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        PropertySetterInterface $productUpdater,
        SaverInterface $productSaver,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        CollectionFilterInterface $productEditDataFilter,
        RemoverInterface $productRemover,
        ProductBuilderInterface $productBuilder
    ) {
        $this->productRepository     = $productRepository;
        $this->attributeRepository   = $attributeRepository;
        $this->productUpdater        = $productUpdater;
        $this->productSaver          = $productSaver;
        $this->normalizer            = $normalizer;
        $this->validator             = $validator;
        $this->userContext           = $userContext;
        $this->objectFilter          = $objectFilter;
        $this->productEditDataFilter = $productEditDataFilter;
        $this->productRemover        = $productRemover;
        $this->productBuilder        = $productBuilder;
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
        $product  = $this->findProductOr404($id);
        $this->productBuilder->addMissingAssociations($product);
        $channels = array_keys($this->userContext->getChannelChoicesWithUserChannel());
        $locales  = $this->userContext->getUserLocaleCodes();

        return new JsonResponse(
            $this->normalizer->normalize(
                $product,
                'internal_api',
                [
                    'locales'     => $locales,
                    'channels'    => $channels,
                    'filter_type' => 'pim.internal_api.product_value.view'
                ]
            )
        );
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

        // TODO: PEF should never update groups, no way to do so from the screen, if a product is added to
        // another group during the save, this relation will be removed, other issue is that variant groups are never
        // passed here, so a product is always removed from it's variant group when saved
        unset($data['groups']);

        $this->updateProduct($product, $data);

        $violations = $this->validator->validate($product);

        if (0 === $violations->count()) {
            $this->productSaver->save($product);

            return new JsonResponse($this->normalizer->normalize($product, 'internal_api'));
        } else {
            $errors = $this->transformViolations($violations, $product);

            return new JsonResponse($errors, 400);
        }
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
     * Remove an optional attribute form a product
     *
     * @param int $productId   The product id
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
    public function removeAttributeAction($productId, $attributeId)
    {
        $product = $this->findProductOr404($productId);
        if ($this->objectFilter->filterObject($product, 'pim.internal_api.product.edit')) {
            throw new AccessDeniedHttpException();
        }

        $attribute = $this->findAttributeOr404($attributeId);

        if (!$product->isAttributeRemovable($attribute)) {
            throw new BadRequestHttpException();
        }

        $this->productBuilder->removeAttributeFromProduct($product, $attribute);
        $this->productSaver->save($product, ['recalculate' => false, 'schedule' => false]);

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
        foreach ($data as $item => $itemData) {
            if ('values' === $item) {
                foreach ($itemData as $attributeCode => $values) {
                    foreach ($values as $value) {
                        $this->productUpdater->setData(
                            $product,
                            $attributeCode,
                            $value['data'],
                            [
                                'locale' => $value['locale'],
                                'scope'  => $value['scope']
                            ]
                        );
                    }
                }
            } else {
                $this->productUpdater->setData(
                    $product,
                    $item,
                    $itemData
                );
            }
        }
    }

    /**
     * Transforms product violations into an array
     *
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     *
     * @return array
     */
    protected function transformViolations(ConstraintViolationListInterface $violations, ProductInterface $product)
    {
        $errors = [];
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if (0 === strpos($path, 'values')) {
                $codeStart  = strpos($path, '[') + 1;
                $codeLength = strpos($path, ']') - $codeStart;

                $valueIndex = substr($path, $codeStart, $codeLength);
                $value = $product->getValues()[$valueIndex];
                $attributeCode = $value->getAttribute()->getCode();

                $currentError = [
                    'attribute'     => $attributeCode,
                    'locale'        => $value->getLocale(),
                    'scope'         => $value->getScope(),
                    'message'       => $violation->getMessage(),
                    'invalid_value' => $violation->getInvalidValue()
                ];

                $errors['values'][$attributeCode] = isset($errors['values'][$attributeCode])
                    ? $errors['values'][$attributeCode]
                    : [];

                $identicalErrors = array_filter(
                    $errors['values'][$attributeCode],
                    function ($error) use ($currentError) {
                        return isset($error['message']) && $error['message'] === $currentError['message'];
                    }
                );

                if (empty($identicalErrors)) {
                    $errors['values'][$attributeCode][] = $currentError;
                }
            } else {
                $errors[$path] = [
                    'message'       => $violation->getMessage(),
                    'invalid_value' => $violation->getInvalidValue()
                ];
            }
        }

        return $errors;
    }
}
