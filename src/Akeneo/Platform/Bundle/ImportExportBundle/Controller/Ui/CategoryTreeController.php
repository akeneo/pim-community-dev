<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui;

use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CategoryTree;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CountCategoriesPerTree;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\FindCategoryTrees;
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
    private CountCategoriesPerTree $countCategoriesPerTree;

    public function __construct(FindCategoryTrees $findCategoryTrees, CountCategoriesPerTree $countCategoriesPerTree)
    {
        $this->findCategoryTrees = $findCategoryTrees;
        $this->countCategoriesPerTree = $countCategoriesPerTree;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $selectedCategoryCodes = $this->categoryCodes($request);
        $normalizedCategoryTrees = $this->normalizedCategoryTreesWithSelectedCount($selectedCategoryCodes);

        return new JsonResponse($normalizedCategoryTrees);
    }

    /**
     * @param string[] $selectedCategoryCodes
     */
    private function normalizedCategoryTreesWithSelectedCount(array $selectedCategoryCodes): array
    {
        $selectedCategoryCountPerTree = $this->countCategoriesPerTree->executeWithoutChildren($selectedCategoryCodes);
        return array_map(
            static function (CategoryTree $categoryTree) use ($selectedCategoryCountPerTree) {
                $result = $categoryTree->normalize();
                $result['selectedCategoryCount'] = $selectedCategoryCountPerTree[$result['code']];

                return $result;
            },
            $this->findCategoryTrees->execute()
        );
    }

    private function categoryCodes(Request $request): array
    {
        return json_decode($request->getContent(), true) ?? [];
    }
}
