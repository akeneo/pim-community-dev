<?php

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Sql\Update;

use Akeneo\Category\Application\Query\GetAllEnrichedCategoryValuesByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

class UpdateCategoryEnrichedValuesSqlIntegration extends CategoryTestCase
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

    public function testItUpdateCategoriesWithNewValueCollection(): void
    {
        $valuesByCategoryCode = $this->get(GetAllEnrichedCategoryValuesByCategoryCode::class)->execute();

        $socksValueCollection = json_decode($valuesByCategoryCode['socks'], true);
        $shoesValueCollection = json_decode($valuesByCategoryCode['shoes'], true);

        // add 'print' channel value to 'title' attribute
        $socksValueCollection[$this->getPrintTitleKey()] = [
            'data' => 'Socks you need',
            'type' => 'text',
            'channel' => 'print',
            'locale' => 'en_US',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];

        // add 'mobile' channel value to 'title' attribute
        $shoesValueCollection[$this->getMobileTitleKey()] = [
            'data' => 'Shoes you need',
            'type' => 'text',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];

        $updatedBatch =  [
            'socks' => json_encode($socksValueCollection),
            'shoes' => json_encode($shoesValueCollection),
        ];

        $this->get(UpdateCategoryEnrichedValues::class)->execute($updatedBatch);

        $updatedValuesByCategoryCode = $this->get(GetAllEnrichedCategoryValuesByCategoryCode::class)->execute();

        $updatedSocksValueCollection = json_decode($updatedValuesByCategoryCode['socks'], true);
        $this->assertEquals(
            'Socks you need',
            $updatedSocksValueCollection[$this->getPrintTitleKey()]['data']
        );
        $this->assertEquals(
            'print',
            $updatedSocksValueCollection[$this->getPrintTitleKey()]['channel']
        );
        $this->assertEquals(
            'All the shoes you need!',
            $updatedSocksValueCollection[$this->getEcommerceTitleKey()]['data']
        );

        $updatedShoesValueCollection = json_decode($updatedValuesByCategoryCode['shoes'], true);
        $this->assertEquals(
            'Shoes you need',
            $updatedShoesValueCollection[$this->getMobileTitleKey()]['data']
        );
        $this->assertEquals(
            'mobile',
            $updatedShoesValueCollection[$this->getMobileTitleKey()]['channel']
        );
        $this->assertEquals(
            'All the shoes you need!',
            $updatedShoesValueCollection[$this->getEcommerceTitleKey()]['data']
        );
    }

    private function getPrintTitleKey(): string
    {
        return 'title'
            .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
            .AbstractValue::SEPARATOR.'print'
            .AbstractValue::SEPARATOR.'en_US';
    }

    private function getMobileTitleKey(): string
    {
        return 'title'
            .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
            .AbstractValue::SEPARATOR.'mobile'
            .AbstractValue::SEPARATOR.'en_US';
    }

    private function getEcommerceTitleKey(): string
    {
        return 'title'
            .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
            .AbstractValue::SEPARATOR.'ecommerce'
            .AbstractValue::SEPARATOR.'en_US';
    }
}
