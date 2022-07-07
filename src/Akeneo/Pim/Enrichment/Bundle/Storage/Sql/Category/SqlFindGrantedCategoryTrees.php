<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Category\Api\CategoryTree;
use Akeneo\Category\Api\FindGrantedCategoryTrees;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;

class SqlFindGrantedCategoryTrees implements FindGrantedCategoryTrees
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private TranslationNormalizer $translationNormalizer,
        private CollectionFilterInterface $collectionFilter
    ) {
    }

    /**
     * @return CategoryTree[]
     */
    public function execute(): array
    {
        $categories = $this->categoryRepository->findBy(['parent' => null]);

        $categories = $this->applyPermissions($categories);

        return $this->categoryTrees($categories);
    }

    /**
     * @param Category[] $categories
     * @return Category[]
     */
    private function applyPermissions(array $categories): array
    {
        return $this->collectionFilter->filterCollection($categories, 'pim.internal_api.product_category.view');
    }

    /**
     * @param Category[] $categories
     * @return CategoryTree[]
     */
    private function categoryTrees(array $categories): array
    {
        $translationNormalizer = $this->translationNormalizer;

        return array_map(
            static function (Category $category) use ($translationNormalizer) {
                $categoryTree = new CategoryTree();
                $categoryTree->code = $category->getCode();
                $categoryTree->labels = $translationNormalizer->normalize($category, 'standard');
                $categoryTree->id = $category->getId();

                return $categoryTree;
            },
            $categories
        );
    }
}
