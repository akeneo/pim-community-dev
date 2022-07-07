<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui;

use Akeneo\Category\Api\CategoryTree;
use Akeneo\Category\Api\FindGrantedCategoryTrees;
use Akeneo\Category\Api\GetCategoryChildrenCodesPerTreeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController
{
    private FindGrantedCategoryTrees $findGrantedCategoryTrees;
    private GetCategoryChildrenCodesPerTreeInterface $getCategoryChildrenCodesPerTree;

    public function __construct(
        FindGrantedCategoryTrees $findGrantedCategoryTrees,
        GetCategoryChildrenCodesPerTreeInterface $getCategoryChildrenCodesPerTree
    ) {
        $this->findGrantedCategoryTrees = $findGrantedCategoryTrees;
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
            $this->findGrantedCategoryTrees->execute()
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
