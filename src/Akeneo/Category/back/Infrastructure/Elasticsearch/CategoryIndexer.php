<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Elasticsearch;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;

class CategoryIndexer
{
    public function __construct(
        private Client $categoryClient,
        private CategoryRepositoryInterface $categoryRepository,
        private TranslationNormalizer $translationNormalizer
    ) {
    }

    public function index(int $categoryId): void
    {
        /** @var CategoryInterface $category */
        $category = $this->categoryRepository->find($categoryId);

        $normalizedCategory = [
            'id' => 'category_' . $category->getId(),
            'code' => $category->getCode(),
            'updated_at' => $category->getUpdated()->format('c'),
            'parent_id' => $category->getParent() ? 'category_' . $category->getParent()->getId() : null,
            'parent_code' => $category->getParent() ? $category->getParent()->getCode() : null,
            'level' => $category->getLevel(),
            'category_code_label_search' => $this->getCodeLabelMatrix($category),
        ];

        $this->categoryClient->index($normalizedCategory['id'], $normalizedCategory, refresh::disable());
    }

    private function getCodeLabelMatrix(CategoryInterface $category): array
    {
        $labels = $this->translationNormalizer->normalize($category, 'standard');

        $codeLabelMatrix = [];
        foreach ($labels as $localeCode => $label) {
            $codeLabelMatrix[$localeCode] = trim(sprintf('%s %s', $category->getCode(), $label));
        }

        return $codeLabelMatrix;
    }
}
