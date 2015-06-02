<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRestController
{
    /** @var ProductManager */
    protected $productManager;

    /** @var AttributeManager */
    protected $attributeManager;

    /** @var ProductUpdaterInterface */
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

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var LocaleManager */
    protected $localeManager;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ProductManager               $productManager
     * @param AttributeManager             $attributeManager
     * @param ProductUpdaterInterface      $productUpdater
     * @param SaverInterface               $productSaver
     * @param NormalizerInterface          $normalizer
     * @param ValidatorInterface           $validator
     * @param UserContext                  $userContext
     * @param ObjectFilterInterface        $objectFilter
     * @param SecurityFacade               $securityFacade
     * @param LocaleManager                $localeManager
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ProductManager $productManager,
        AttributeManager $attributeManager,
        ProductUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        SecurityFacade $securityFacade,
        LocaleManager $localeManager,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->productManager      = $productManager;
        $this->attributeManager    = $attributeManager;
        $this->productUpdater      = $productUpdater;
        $this->productSaver        = $productSaver;
        $this->normalizer          = $normalizer;
        $this->validator           = $validator;
        $this->userContext         = $userContext;
        $this->objectFilter        = $objectFilter;
        $this->securityFacade      = $securityFacade;
        $this->localeManager       = $localeManager;
        $this->attributeRepository = $attributeRepository;
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
        $channels = array_keys($this->userContext->getChannelChoicesWithUserChannel());
        $locales  = $this->userContext->getUserLocaleCodes();

        return new JsonResponse(
            $this->normalizer->normalize(
                $product,
                'internal_api',
                [
                    'locales'     => $locales,
                    'channels'    => $channels,
                    'filter_type' => 'pim:internal_api:product_value:view'
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
        if ($this->objectFilter->filterObject($product, 'pim:internal_api:product:edit')) {
            throw new AccessDeniedHttpException();
        }

        $data = json_decode($request->getContent(), true);
        $data = $this->filterProductDataForEdit($data);

        $this->updateProduct($product, $data);

        $violations = $this->validateProduct($product);

        if (0 === $violations->count()) {
            $this->productSaver->save($product);

            return new JsonResponse($this->normalizer->normalize($product, 'internal_api'));
        } else {
            $errors = $this->transformViolations($violations, $product);

            return new JsonResponse($errors, 400);
        }
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
        if ($this->objectFilter->filterObject($product, 'pim:internal_api:product:edit')) {
            throw new AccessDeniedHttpException();
        }

        $attribute = $this->findAttributeOr404($attributeId);

        if (!$product->isAttributeRemovable($attribute)) {
            throw new BadRequestHttpException();
        }

        $this->productManager->removeAttributeFromProduct($product, $attribute);

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
        $product = $this->productManager->find($id);
        $product = $this->objectFilter->filterObject($product, 'pim:internal_api:product:view') ? null : $product;

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
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
        $attribute = $this->attributeManager->getAttribute($id);

        if (!$attribute) {
            throw new NotFoundHttpException(
                sprintf('Attribute with id %s could not be found.', (string) $id)
            );
        }

        return $attribute;
    }

    /**
     * TODO: Registries for locales and attribute groups
     * TODO: OptionsResolver to ensure values shape ?
     *
     * Filter product data that cannot be edited
     *
     * @param array $productData
     *
     * @return array
     */
    protected function filterProductDataForEdit(array $productData)
    {
        $filteredProductData = [];

        foreach ($productData as $type => $data) {
            if ('values' === $type) {
                $filteredValues = [];

                foreach ($data as $attributeCode => $contextValues) {
                    $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
                    if (!$this->objectFilter->filterObject($attribute, 'pim:internal_api:attribute:edit')) {
                        $filteredContextValues = [];

                        foreach ($contextValues as $contextValue) {
                            $locale = $this->localeManager->getLocaleByCode($contextValue['locale']);
                            if (!$this->objectFilter->filterObject($locale, 'pim:internal_api:locale:edit')) {
                                $filteredContextValues[] = $contextValue;
                            }
                        }

                        $filteredValues[$attributeCode] = $filteredContextValues;
                    }
                }

                $filteredProductData['values'] = array_filter($filteredValues);
            } else {
                switch ($type) {
                    case 'family':
                        $acl = 'pim_enrich_product_change_family';
                        break;
                    case 'groups':
                        $acl = 'pim_enrich_product_add_to_groups';
                        break;
                    case 'categories':
                        $acl = 'pim_enrich_product_categories_view';
                        break;
                    case 'enabled':
                        $acl = 'pim_enrich_product_change_state';
                        break;
                    case 'associations':
                        $acl = 'pim_enrich_associations_view';
                        break;
                    default:
                        $acl = null;
                }

                if (null === $acl || $this->securityFacade->isGranted($acl)) {
                    $filteredProductData[$type] = $data;
                }
            }
        }

        return $filteredProductData;
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
                            $value['value'],
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
     * Validates a product
     *
     * @param ProductInterface $product
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateProduct(ProductInterface $product)
    {
        $violations = $this->validator->validate($product);

        if (0 === $violations->count()) {
            $violations = $this->validator->validate($product->getValues());
        }

        return $violations;
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

                $errors['values'][$value->getAttribute()->getCode()][] = [
                    'attribute'     => $value->getAttribute()->getCode(),
                    'locale'        => $value->getLocale(),
                    'scope'         => $value->getScope(),
                    'message'       => $violation->getMessage(),
                    'invalid_value' => $violation->getInvalidValue()
                ];
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
