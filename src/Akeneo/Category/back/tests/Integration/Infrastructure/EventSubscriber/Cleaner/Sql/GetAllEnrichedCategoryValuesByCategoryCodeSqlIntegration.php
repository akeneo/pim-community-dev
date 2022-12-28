<?php

namespace Akeneo\Test\Category\Integration\Infrastructure\EventSubscriber\Cleaner\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\Sql\GetAllEnrichedCategoryValuesByCategoryCode;
use Akeneo\Test\Integration\Configuration;

class GetAllEnrichedCategoryValuesByCategoryCodeSqlIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {

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

    public function testItGetAllCategoryWithEnrichedValuesSortedByCategoryCode()
    {

        // TODO fix json expected
        $expectedCodes = ['shoes', 'socks'];

        $valuesByCategoryCode = $this->get(GetAllEnrichedCategoryValuesByCategoryCode::class)->execute();

        $this->assertEqualsCanonicalizing($expectedCodes, array_keys($valuesByCategoryCode));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
