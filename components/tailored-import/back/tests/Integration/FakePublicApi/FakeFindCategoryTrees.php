<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\FakePublicApi;

use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CategoryTree;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\FindCategoryTrees;

class FakeFindCategoryTrees implements FindCategoryTrees
{
    public function execute(): array
    {
        $masterCategoryTree = new CategoryTree();
        $masterCategoryTree->id = 2;
        $masterCategoryTree->code = 'master';
        $masterCategoryTree->labels = [
            'en_US' => 'Master',
            'fr_FR' => 'Master',
            'de_DE' => 'Master',
        ];

        $printCategoryTree = new CategoryTree();
        $printCategoryTree->id = 23;
        $printCategoryTree->code = 'print';
        $printCategoryTree->labels = [
            'en_US' => 'Print',
            'fr_FR' => 'Print',
            'de_DE' => 'Print',
        ];

        $suppliersCategoryTree = new CategoryTree();
        $suppliersCategoryTree->id = 27;
        $suppliersCategoryTree->code = 'suppliers';
        $suppliersCategoryTree->labels = [
            'en_US' => 'Suppliers',
            'fr_FR' => 'Suppliers',
            'de_DE' => 'Suppliers',
        ];

        return [$masterCategoryTree, $printCategoryTree, $suppliersCategoryTree];
    }
}
