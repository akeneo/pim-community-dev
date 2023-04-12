<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Infrastructure\Persistence\Attribute\SearchAttributeOptionsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Attribute\SearchAttributeOptionsQuery
 */
class SearchAttributeOptionsQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsPaginatedAttributeOptions(): void
    {
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);

        $result = self::getContainer()->get(SearchAttributeOptionsQuery::class)->execute('clothing_size', 'en_US', search: null, page: 1, limit: 2);
        $this->assertEquals([
            [
                'code' => 'xs',
                'label' => 'XS',
            ],
            [
                'code' => 's',
                'label' => 'S',
            ],
        ], $result);

        $result = self::getContainer()->get(SearchAttributeOptionsQuery::class)->execute('clothing_size', 'en_US', search: null, page: 2, limit: 2);
        $this->assertEquals([
            [
                'code' => 'm',
                'label' => 'M',
            ],
            [
                'code' => 'l',
                'label' => 'L',
            ],
        ], $result);
    }

    public function testItReturnsMatchingAttributeOptions(): void
    {
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);

        $result = self::getContainer()->get(SearchAttributeOptionsQuery::class)->execute('clothing_size', 'en_US', search: 'X');
        $this->assertEquals([
            [
                'code' => 'xs',
                'label' => 'XS',
            ],
            [
                'code' => 'xl',
                'label' => 'XL',
            ],
        ], $result);
    }

    public function testItReturnsAttributeOptionsEvenWhenLocaleIsDisabled(): void
    {
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);

        $result = self::getContainer()->get(SearchAttributeOptionsQuery::class)->execute('clothing_size', 'jp_JP', search: null, page: 1, limit: 1);
        $this->assertEquals([
            [
                'code' => 'xs',
                'label' => '[xs]',
            ],
        ], $result);
    }
}
