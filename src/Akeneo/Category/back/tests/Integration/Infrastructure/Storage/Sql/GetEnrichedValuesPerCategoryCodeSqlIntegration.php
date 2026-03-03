<?php

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetEnrichedValuesPerCategoryCode;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;

class GetEnrichedValuesPerCategoryCodeSqlIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $categorySocks = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $categoryShoes = $this->createCategory([
            'code' => 'shoes',
            'labels' => [
                'fr_FR' => 'Chaussures',
                'en_US' => 'Shoes'
            ]
        ]);

        $this->createCategory([
            'code' => 'pants',
            'labels' => [
                'fr_FR' => 'Pantalons',
                'en_US' => 'Pants'
            ]
        ]);

        $this->updateCategoryWithValues($categorySocks->getCode());
        $this->updateCategoryWithValues($categoryShoes->getCode());
    }

    public function testItGetAllCategoryWithEnrichedValuesSortedByCategoryCode(): void
    {
        $fetchedCategories = [];

        foreach ($this->get(GetEnrichedValuesPerCategoryCode::class)->byBatchesOf(1) as $valuesByCategoryCode){
            $fetchedCategories[] = $valuesByCategoryCode;
        }

        $this->assertCount(2, $fetchedCategories);
        $this->assertArrayHasKey('socks', $fetchedCategories[0]);
        $this->assertArrayHasKey('shoes', $fetchedCategories[1]);
    }
}
