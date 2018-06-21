<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChildCategory
{
    /**
     * @param ReadModel\ChildCategory[] $categories
     *
     * @return array
     */
    public function normalizeList(array $categories): array
    {
        $normalizedCategories = [];

        foreach ($categories as $category) {
            $label = sprintf('%s (%s)', $category->label(), $category->numberProductsInCategory());

            $normalizedCategories[] = [
                'attr' => [
                    'id' => 'node_' . $category->id(),
                    'data-code' => $category->code(),
                ],
                'data' => $label,
                'state' => $this->state($category),
                'children' => $this->normalizeList($category->childrenCategoriesToExpand()),
            ];
        }

        return $normalizedCategories;
    }

    /**
     * Ideally, CSS calculation should be done on front-end side.
     *
     * @param ReadModel\ChildCategory $category
     *
     * @return string
     */
    private function state(ReadModel\ChildCategory $category): string
    {
        $state = $category->isLeaf() ? 'leaf' : 'closed';
        if ($category->expanded()) {
            $state = 'open';
        }

        if ($category->selectedAsFilter()) {
            $state .= ' toselect jstree-checked';
        }

        return $state;
    }
}
