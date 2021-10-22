<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetCategoryChildrenCodesQuery;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryChildrenCodesQueryIntegration extends TestCase
{
    public function test_it_returns_the_codes_of_a_category_and_its_children()
    {
        $expectedCodes[] = $this->createCategory([
            'code' => 'category_A',
            'parent' => 'master',
        ])->getCode();
        $expectedCodes[] = $this->createCategory([
            'code' => 'children_A_1',
            'parent' => 'category_A',
        ])->getCode();
        $expectedCodes[] = $this->createCategory([
            'code' => 'children_A_2',
            'parent' => 'category_A',
        ])->getCode();
        $expectedCodes[] = $this->createCategory([
            'code' => 'sub_children_A',
            'parent' => 'children_A_1',
        ])->getCode();

        $this->createCategory([
            'code' => 'other_category',
            'parent' => 'master',
        ]);

        $categoryCodes = $this->get(GetCategoryChildrenCodesQuery::class)->execute(new CategoryCode('category_A'));

        $this->assertEqualsCanonicalizing($expectedCodes, $categoryCodes);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
