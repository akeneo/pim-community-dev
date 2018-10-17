<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Twig\CategoryExtension;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
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

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param CategoryRepositoryInterface $repository
     * @param CategoryExtension           $twigExtension
     * @param NormalizerInterface         $normalizer
     * @param ObjectFilterInterface       $objectFilter
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        CategoryExtension $twigExtension,
        NormalizerInterface $normalizer,
        ObjectFilterInterface $objectFilter
    ) {
        $this->repository = $repository;
        $this->twigExtension = $twigExtension;
        $this->normalizer = $normalizer;
        $this->objectFilter = $objectFilter;
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

        $selectedCategories = $this->repository->getCategoriesByCodes($request->get('selected', []));
        if (0 !== $selectedCategories->count()) {
            $tree = $this->twigExtension->listCategoriesResponse(
                $this->repository->getFilledTree($parent, $selectedCategories),
                $selectedCategories
            );
        } else {
            $tree = $this->twigExtension->listCategoriesResponse(
                $this->repository->getFilledTree($parent, new ArrayCollection([$parent])),
                new ArrayCollection()
            );
        }

        // Returns only children of the given category without the node itself
        if (!empty($tree)) {
            $tree = $tree[0]['children'];
        }

        return new JsonResponse($tree);
    }

    /**
     * List root categories
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $categories = $this->repository->findBy(
            [
                'parent' => null,
            ]
        );

        $categories = $this->objectFilter->filterCollection($categories, 'pim.internal_api.product_category.view');

        return new JsonResponse(
            $this->normalizer->normalize($categories, 'internal_api')
        );
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $category = $this->repository->findOneByIdentifier($identifier);

        $normalizedCategory = $this->normalizer->normalize($category, 'internal_api');

        return new JsonResponse($normalizedCategory);
    }
}
