<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetOptionDetailsIntegration extends TestCase
{
    /** @test */
    public function itReturnsAnEmptyIteratorWhenThereIsNoTableAttribute()
    {
        $query = $this->get('Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetSelectOptionDetails');
        $results = $query();
        Assert::assertIsIterable($results);

        Assert::assertFalse($results->valid());
        Assert::assertNull($results->current());
    }

    /** @test */
    public function itListsTableSelectOptionDetails()
    {
        $this->activateLocalesForChannel('ecommerce', ['en_US', 'fr_FR']);
        $this->createTableAttribute('packaging', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'dimension', 'options' => [
                ['code' => 'width', 'labels' => ['en_US' => 'Width']],
                ['code' => 'height', 'labels' => ['en_US' => 'Height', 'fr_FR' => 'Hauteur']],
                ['code' => 'depth'],
            ]],
            ['data_type' => NumberColumn::DATATYPE, 'code' => 'measure'],
        ]);
        $this->createTableAttribute('nutrition', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'ingredient', 'options' => [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'sugar', 'labels' => ['en_US' => 'Sugar']],
                ['code' => 'egg'],
            ]],
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'score', 'options' => [
                ['code' => 'D', 'labels' => ['en_US' => 'D']],
                ['code' => 'B', 'labels' => ['en_US' => 'B']],
                ['code' => 'C', 'labels' => ['en_US' => 'C']],
                ['code' => 'E', 'labels' => ['en_US' => 'E']],
                ['code' => 'A', 'labels' => ['en_US' => 'A']],
            ]],
        ]);

        $query = $this->get('Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetSelectOptionDetails');
        $results = $query();

        Assert::assertIsIterable($results);
        $actualResults = \is_array($results) ? $results : \iterator_to_array($results);

        $expectedResults = [
            ['attribute_code' => 'nutrition', 'column_code' => 'ingredient', 'option_code' => 'egg', 'labels' => []],
            ['attribute_code' => 'nutrition', 'column_code' => 'ingredient', 'option_code' => 'salt', 'labels' => ['en_US' => 'Salt']],
            ['attribute_code' => 'nutrition', 'column_code' => 'ingredient', 'option_code' => 'sugar', 'labels' => ['en_US' => 'Sugar']],
            ['attribute_code' => 'nutrition', 'column_code' => 'score', 'option_code' => 'A', 'labels' => ['en_US' => 'A']],
            ['attribute_code' => 'nutrition', 'column_code' => 'score', 'option_code' => 'B', 'labels' => ['en_US' => 'B']],
            ['attribute_code' => 'nutrition', 'column_code' => 'score', 'option_code' => 'C', 'labels' => ['en_US' => 'C']],
            ['attribute_code' => 'nutrition', 'column_code' => 'score', 'option_code' => 'D', 'labels' => ['en_US' => 'D']],
            ['attribute_code' => 'nutrition', 'column_code' => 'score', 'option_code' => 'E', 'labels' => ['en_US' => 'E']],
            ['attribute_code' => 'packaging', 'column_code' => 'dimension', 'option_code' => 'depth', 'labels' => []],
            ['attribute_code' => 'packaging', 'column_code' => 'dimension', 'option_code' => 'height', 'labels' => ['en_US' => 'Height', 'fr_FR' => 'Hauteur']],
            ['attribute_code' => 'packaging', 'column_code' => 'dimension', 'option_code' => 'width', 'labels' => ['en_US' => 'Width']],
        ];

        Assert::assertSameSize($expectedResults, $actualResults);

        foreach ($actualResults as $key => $actualResult) {
            Assert::assertInstanceOf(SelectOptionDetails::class, $actualResult);
            Assert::assertArrayHasKey($key, $expectedResults);
            Assert::assertSame($expectedResults[$key]['attribute_code'], $actualResult->attributeCode());
            Assert::assertSame($expectedResults[$key]['column_code'], $actualResult->columnCode());
            Assert::assertSame($expectedResults[$key]['option_code'], $actualResult->optionCode());
            Assert::assertSame($expectedResults[$key]['labels'], $actualResult->labels());
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createTableAttribute(string $attributeCode, array $tableConfig): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => $attributeCode,
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => $tableConfig,
        ]);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, (string)$violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function activateLocalesForChannel(string $channelCode, array $localeCodes): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        if (null === $channel) {
            $channel = $this->get('pim_catalog.factory.channel')->create();
        }

        $this->get('pim_catalog.updater.channel')->update($channel, [
            'code' => $channelCode,
            'locales' => $localeCodes,
            'currencies' => ['USD'],
            'category_tree' => 'master',
        ]);
        $errors = $this->get('validator')->validate($channel);
        \Webmozart\Assert\Assert::count($errors, 0, sprintf('Error: %s', $errors));

        $this->get('pim_catalog.saver.channel')->save($channel);
        // Kill background process to avoid a race condition during loading fixtures for the next integration test.
        exec('pkill -f "pim:catalog:remove-completeness-for-channel-and-locale"');
    }
}
