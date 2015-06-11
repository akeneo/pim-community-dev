<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
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
class GroupRestController
{
    /** @staticvar integer The maximum number of group products to be displayed */
    const MAX_PRODUCTS = 5;

    /** @var GroupManager */
    protected $groupManager;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param GroupManager             $groupManager
     * @param GroupRepositoryInterface $groupRepository
     * @param NormalizerInterface      $normalizer
     */
    public function __construct(
        GroupManager $groupManager,
        GroupRepositoryInterface $groupRepository,
        NormalizerInterface $normalizer
    ) {
        $this->groupManager    = $groupManager;
        $this->groupRepository = $groupRepository;
        $this->normalizer      = $normalizer;
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

        $productList = $this->groupManager->getProductList($group, static::MAX_PRODUCTS);

        return new JsonResponse($this->normalizer->normalize($productList, 'internal_api'));
    }
}
