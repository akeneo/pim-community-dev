<?php

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Sql\Update;

use Akeneo\Category\Application\Query\GetEnrichedValuesPerCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

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
        $valuesByCategoryCode = iterator_to_array($this->get(GetEnrichedValuesPerCategoryCode::class)->byBatchesOf(100));

        $socksValueCollection = $valuesByCategoryCode[0]['socks'];
        $shoesValueCollection = $valuesByCategoryCode[0]['shoes'];

        // add 'print' channel value to 'title' attribute
        $socksValueCollection->setValue(
            TextValue::fromArray(
                [
            'data' => 'Socks you need',
            'type' => 'text',
            'channel' => 'print',
            'locale' => 'en_US',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
                ]
            )
        );

        // add 'mobile' channel value to 'title' attribute
        $shoesValueCollection->setValue(
            TextValue::fromArray(
                [
                    'data' => 'Shoes you need',
                    'type' => 'text',
                    'channel' => 'mobile',
                    'locale' => 'en_US',
                    'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
                ]
            )
        );

        $updatedBatch =  [
            'socks' => $socksValueCollection,
            'shoes' => $shoesValueCollection,
        ];

        $this->get(UpdateCategoryEnrichedValues::class)->execute($updatedBatch);

        $updatedValuesByCategoryCode = iterator_to_array($this->get(GetEnrichedValuesPerCategoryCode::class)->byBatchesOf(100));

        /** @var ValueCollection $updatedSocksValueCollection */
        $updatedSocksValueCollection = $updatedValuesByCategoryCode[0]['socks'];
        $updatedSocksPrintTitleValue = $updatedSocksValueCollection->getValue(
            'title',
            '87939c45-1d85-4134-9579-d594fff65030',
            'print',
            'en_US'
        );
        $this->assertInstanceOf(TextValue::class, $updatedSocksPrintTitleValue);
            $this->assertEquals(
                'Socks you need',
                $updatedSocksPrintTitleValue->getValue()
            );
        $updatedSocksEcommerceTitleValue = $updatedSocksValueCollection->getValue(
            'title',
            '87939c45-1d85-4134-9579-d594fff65030',
            'ecommerce',
            'en_US'
        );
        $this->assertInstanceOf(TextValue::class, $updatedSocksEcommerceTitleValue);
        $this->assertEquals(
            'All the shoes you need!',
                $updatedSocksEcommerceTitleValue->getValue()
        );

        // TODO check les valeurs telles que channel, locale ? et/ou code ?

        $updatedShoesValueCollection = $updatedValuesByCategoryCode[0]['shoes'];
        $this->assertEquals(
            'Shoes you need',
            $updatedShoesValueCollection->getValue(
                'title',
                '87939c45-1d85-4134-9579-d594fff65030',
                'mobile',
                'en_US'
            )->getValue()
        );
        $updatedSocksValue = $updatedSocksValueCollection->getValue(
            'title',
            '87939c45-1d85-4134-9579-d594fff65030',
            'ecommerce',
            'en_US'
        );
        $this->assertInstanceOf(TextValue::class, $updatedSocksValue);
        $this->assertEquals(
            'All the shoes you need!',
            $updatedSocksValue->getValue()
        );
    }
}
