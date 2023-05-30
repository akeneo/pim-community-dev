<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Query\GetCategoryChildrenIds;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Twig\CategoryExtension;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
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
    public function __construct(
        protected CategoryRepositoryInterface $repository,
        protected CategoryExtension $twigExtension,
        protected NormalizerInterface $normalizer,
        protected CollectionFilterInterface $collectionFilter,
        protected GetCategoryChildrenIds $getCategoryChildrenIds,
    ) {
    }

    /**
     * List children categories.
     *
     * @param Request $request The request object
     * @param int $identifier The parent category identifier
     */
    public function listSelectedChildrenAction(Request $request, $identifier): JsonResponse
    {
        $parent = $this->repository->findOneByIdentifier($identifier);

        if (null === $parent) {
            return new JsonResponse(null, 404);
        }

        $selectedCategories = $this->repository->getCategoriesByCodes($request->get('selected', []));
        if (0 !== $selectedCategories->count()) {
            $tree = $this->twigExtension->listCategoriesResponse(
                $this->repository->getFilledTree($parent, $selectedCategories),
                $selectedCategories,
            );
        } else {
            $tree = $this->twigExtension->listCategoriesResponse(
                $this->repository->getFilledTree($parent, new ArrayCollection([$parent])),
                new ArrayCollection(),
            );
        }

        // Returns only children of the given category without the node itself
        if (!empty($tree)) {
            $tree = $tree[0]['children'];
        }

        return new JsonResponse($tree);
    }

    /**
     * List root categories.
     */
    public function listAction(): JsonResponse
    {
        $categories = $this->repository->findBy(
            [
                'parent' => null,
            ],
        );

        $categories = $this->collectionFilter->filterCollection($categories, 'pim.internal_api.product_category.view');

        return new JsonResponse(
            $this->normalizer->normalize($categories, 'internal_api'),
        );
    }

    public function getAction(string $identifier): JsonResponse
    {
        $category = $this->repository->findOneByIdentifier($identifier);

        $normalizedCategory = $this->normalizer->normalize($category, 'internal_api');

        return new JsonResponse($normalizedCategory);
    }

    public function listChildren(int $id): JsonResponse
    {
        $categoryIds = ($this->getCategoryChildrenIds)($id);

        return new JsonResponse($categoryIds);
    }
}
