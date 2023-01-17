<?php

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;

class GetEnrichedCategoryValuesOrderedByCategoryCodeSqlIntegration extends CategoryTestCase
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
        $valuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100,0);

        $this->assertTrue(in_array('socks', array_keys($valuesByCategoryCode)));
        $this->assertTrue(in_array('shoes', array_keys($valuesByCategoryCode)));
        $this->assertFalse(in_array('pants', array_keys($valuesByCategoryCode)));
    }
}
