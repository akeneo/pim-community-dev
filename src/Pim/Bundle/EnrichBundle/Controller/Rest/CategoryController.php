<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\EnrichBundle\Twig\CategoryExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * CategoryController
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryController
{
    /** @var CategoryRepositoryInterface */
    protected $repository;

    /** @var CategoryExtension */
    protected $twigExtension;

    /**
     * @param CategoryRepositoryInterface $repository
     * @param CategoryExtension $twigExtension
     */
    public function __construct(CategoryRepositoryInterface $repository, CategoryExtension $twigExtension)
    {
        $this->repository = $repository;
        $this->twigExtension = $twigExtension;
    }

    /**
     * List children categories
     *
     * @param Request $request    The request object
     * @param int     $identifier The parent category identifier
     *
     * @return array
     */
    public function listSelectedChildrenAction(Request $request, $identifier)
    {
        $parent = $this->repository->findOneByIdentifier($identifier);

        if (null === $parent) {
            return new JsonResponse(null, 404);
        }

        $categories = $this->repository->getCategoriesByCodes($request->get('selected', []));
        $tree = $this->twigExtension->listCategoriesResponse(
            $this->repository->getFilledTree($parent, $categories),
            $categories
        );

        // Exclude root as we when children only
        if (!empty($tree)) {
            $tree = $tree[0]['children'];
        }

        return new JsonResponse($tree);
    }
}
