<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetCategoryChildrenIdsQuery;
use Akeneo\Test\Integration\TestCase;

final class GetCategoryChildrenIdsQueryIntegration extends TestCase
{
    public function test_it_returns_the_ids_of_a_category_and_its_children()
    {
        $expectedIds[] = $this->createCategory([
            'code' => 'category_A',
            'parent' => 'master',
        ])->getId();
        $expectedIds[] = $this->createCategory([
            'code' => 'children_A_1',
            'parent' => 'category_A',
        ])->getId();
        $expectedIds[] = $this->createCategory([
            'code' => 'children_A_2',
            'parent' => 'category_A',
        ])->getId();
        $expectedIds[] = $this->createCategory([
            'code' => 'sub_children_A',
            'parent' => 'children_A_1',
        ])->getId();

        $this->createCategory([
            'code' => 'other_category',
            'parent' => 'master',
        ]);

        $categoryIds = $this->get(GetCategoryChildrenIdsQuery::class)->execute(new CategoryCode('category_A'));

        $this->assertEqualsCanonicalizing($expectedIds, $categoryIds);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
