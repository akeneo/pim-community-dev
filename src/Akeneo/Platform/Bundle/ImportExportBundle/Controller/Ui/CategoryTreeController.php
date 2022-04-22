<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui;

use Akeneo\Category\Infrastructure\Component\Query\PublicApi\CategoryTree;
use Akeneo\Category\Infrastructure\Component\Query\PublicApi\FindCategoryTrees;
use Akeneo\Category\Infrastructure\Component\Query\PublicApi\GetCategoryChildrenCodesPerTreeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController
{
    private FindCategoryTrees $findCategoryTrees;
    private GetCategoryChildrenCodesPerTreeInterface $getCategoryChildrenCodesPerTree;

    public function __construct(
        FindCategoryTrees $findCategoryTrees,
        GetCategoryChildrenCodesPerTreeInterface $getCategoryChildrenCodesPerTree
    ) {
        $this->findCategoryTrees = $findCategoryTrees;
        $this->getCategoryChildrenCodesPerTree = $getCategoryChildrenCodesPerTree;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $selectedCategoryCodes = $this->categoryCodes($request);
        $shouldIncludeChildren = $this->shouldIncludeChildren($request);
        $normalizedCategoryTrees = $this->normalizedCategoryTreesWithSelectedCount($selectedCategoryCodes, $shouldIncludeChildren);

        return new JsonResponse($normalizedCategoryTrees);
    }

    private function categoryCodes(Request $request): array
    {
        return json_decode($request->getContent(), true)['selectedCategoryCodes'] ?? [];
    }

    private function shouldIncludeChildren(Request $request): bool
    {
        return json_decode($request->getContent(), true)['shouldIncludeChildren'] ?? [];
    }

    /**
     * @param string[] $selectedCategoryCodes
     */
    private function normalizedCategoryTreesWithSelectedCount(array $selectedCategoryCodes, bool $shouldIncludeChildren): array
    {
        $selectedCategoryCountPerTree = $this->findCategoryCountPerTree($selectedCategoryCodes, $shouldIncludeChildren);
        return array_map(
            static function (CategoryTree $categoryTree) use ($selectedCategoryCountPerTree) {
                $result = $categoryTree->normalize();
                $result['selectedCategoryCount'] = $selectedCategoryCountPerTree[$result['code']];

                return $result;
            },
            $this->findCategoryTrees->execute()
        );
    }

    private function findCategoryCountPerTree(array $selectedCategoryCodes, bool $shouldIncludeChildren): array
    {
        $categoriesChildrenCodes = $shouldIncludeChildren ?
            $this->getCategoryChildrenCodesPerTree->executeWithChildren($selectedCategoryCodes)
            : $this->getCategoryChildrenCodesPerTree->executeWithoutChildren($selectedCategoryCodes);

        return array_map('count', $categoriesChildrenCodes);
    }
}
