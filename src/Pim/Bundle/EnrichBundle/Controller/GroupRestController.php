<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
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

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param GroupManager        $groupManager
     * @param NormalizerInterface $normalizer
     */
    public function __construct(GroupManager $groupManager, NormalizerInterface $normalizer)
    {
        $this->groupManager = $groupManager;
        $this->normalizer   = $normalizer;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $groups = $this->groupManager->getRepository()->getAllGroupsExceptVariant();

        return new JsonResponse($this->normalizer->normalize($groups, 'internal_api'));
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $group = $this->groupManager->getRepository()->findOneByCode($identifier);

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
        $group = $this->groupManager->getRepository()->findOneByCode($identifier);

        if (!$group) {
            throw new NotFoundHttpException(sprintf('Group with code "%s" not found', $identifier));
        }

        $productList = $this->groupManager->getProductList($group, static::MAX_PRODUCTS);

        return new JsonResponse($this->normalizer->normalize($productList, 'internal_api'));
    }
}
