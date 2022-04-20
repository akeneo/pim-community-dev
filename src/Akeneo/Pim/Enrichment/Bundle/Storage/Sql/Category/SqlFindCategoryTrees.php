<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Category\back\Domain\Model\Category;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CategoryTree;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\FindCategoryTrees;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindCategoryTrees implements FindCategoryTrees
{
    private CategoryRepositoryInterface $categoryRepository;
    private TranslationNormalizer $translationNormalizer;
    private CollectionFilterInterface $collectionFilter;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        TranslationNormalizer $translationNormalizer,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->translationNormalizer = $translationNormalizer;
        $this->collectionFilter = $collectionFilter;
    }

    public function execute(): array
    {
        $categories = $this->categoryRepository->findBy(['parent' => null]);
        $categoriesWithPermissions = $this->applyPermissions($categories);

        return $this->categoryTrees($categoriesWithPermissions);
    }

    /**
     * @param Category[] $categories
     */
    private function applyPermissions(array $categories): array
    {
        return $this->collectionFilter->filterCollection($categories, 'pim.internal_api.product_category.view');
    }

    /**
     * @return CategoryTree[]
     */
    private function categoryTrees(
        array $categoriesWithPermissions
    ): array {
        $translationNormalizer = $this->translationNormalizer;

        return array_map(
            static function (Category $category) use ($translationNormalizer) {
                $categoryTree = new CategoryTree();
                $categoryTree->code = $category->getCode();
                $categoryTree->labels = $translationNormalizer->normalize($category, 'standard');

                return $categoryTree;
            },
            $categoriesWithPermissions
        );
    }
}
