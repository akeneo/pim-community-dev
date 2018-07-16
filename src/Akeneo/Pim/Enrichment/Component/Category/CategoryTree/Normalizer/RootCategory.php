<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootCategory
{
    /**
     * @param ReadModel\RootCategory[] $rootCategories
     *
     * @return array
     */
    public function normalizeList(array $rootCategories): array
    {
        $normalizedCategories = [];

        foreach ($rootCategories as $rootCategory) {
            $label = sprintf('%s (%s)', $rootCategory->label(), $rootCategory->numberProductsInCategory());

            $normalizedCategories[] = [
                'id' => $rootCategory->id(),
                'code' => $rootCategory->code(),
                'label' => $label,
                'selected' => $rootCategory->selected() ? 'true' : 'false',
            ];
        }

        return $normalizedCategories;
    }
}
