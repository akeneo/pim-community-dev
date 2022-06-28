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

use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\GetCategoryChildrenCodesPerTreeInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

class FakeGetCategoryChildrenCodesPerTree implements GetCategoryChildrenCodesPerTreeInterface
{
    const CATEGORY_TREES = [
        'master' => [
            'pc_monitor',
            'shoes',
        ],
        'print' => [
            'printer',
        ],
        'suppliers' => [
            'adidas'
        ]
    ];

    public function executeWithChildren(array $categoryCodes): array
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function executeWithoutChildren(array $categoryCodes): array
    {
        return array_map(
            static fn ($categoryTree) => array_intersect($categoryTree, $categoryCodes),
            self::CATEGORY_TREES
        );
    }
}
