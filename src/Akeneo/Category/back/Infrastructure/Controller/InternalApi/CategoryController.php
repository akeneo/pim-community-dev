<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Query\GetCategoryChildrenIds;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Twig\CategoryExtension;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
