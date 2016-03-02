<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupController
{
    /** @staticvar integer The maximum number of group products to be displayed */
    const MAX_PRODUCTS = 5;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param GroupRepositoryInterface   $groupRepository
     * @param ProductRepositoryInterface $productRepository
     * @param NormalizerInterface        $normalizer
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer
    ) {
        $this->groupRepository   = $groupRepository;
        $this->productRepository = $productRepository;
        $this->normalizer        = $normalizer;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $groups = $this->groupRepository->getAllGroupsExceptVariant();

        return new JsonResponse($this->normalizer->normalize($groups, 'internal_api'));
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $group = $this->groupRepository->findOneBy(['code' => $identifier]);

        return new JsonResponse($this->normalizer->normalize($group, 'internal_api'));
    }

    /**
     * Display the products of a group
     *
     * @param string $identifier
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_product_index")
     */
    public function listProductsAction($identifier)
    {
        $group = $this->groupRepository->findOneBy(['code' => $identifier]);

        if (!$group) {
            throw new NotFoundHttpException(sprintf('Group with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize([
            'products'     => array_values($this->productRepository->getProductsByGroup($group, self::MAX_PRODUCTS)),
            'productCount' => $this->productRepository->getProductCountByGroup($group)
        ], 'internal_api'));
    }
}
