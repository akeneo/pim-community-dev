<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui;

use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CategoryTree;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CountCategoriesPerTree;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\FindCategoryTrees;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    public function __invoke(array $selectedCategories): JsonResponse
    {
        var_dump($selectedCategories);
        error_log($selectedCategories);
        $normalizedCategoryTrees = $this->normalizedCategoryTrees();

        return new JsonResponse($normalizedCategoryTrees);
    }

    private function normalizedCategoryTrees(): array
    {
        return array_map(
            static function (CategoryTree $categoryTree) {
                return $categoryTree->normalize();
            },
            $this->findCategoryTrees->execute()
        );
    }
}
