<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Category\Api\CategoryTree;
use Akeneo\Category\Api\FindCategoryTrees;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindCategoryTrees implements FindCategoryTrees
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private TranslationNormalizer $translationNormalizer
    ) {
    }

    /**
     * @return CategoryTree[]
     */
    public function execute(): array
    {
        $categories = $this->categoryRepository->findBy(['parent' => null]);

        return $this->categoryTrees($categories);
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
