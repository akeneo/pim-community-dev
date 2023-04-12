<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Infrastructure\Persistence\Attribute\GetAttributeOptionsByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Attribute\GetAttributeOptionsByCodeQuery
 */
class GetAttributeOptionsByCodeQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsAttributeOptions(): void
    {
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);

        $result = self::getContainer()->get(GetAttributeOptionsByCodeQuery::class)->execute('clothing_size', ['xs', 'm', 'xl'], 'en_US');
        $this->assertEquals([
            [
                'code' => 'xs',
                'label' => 'XS',
            ],
            [
                'code' => 'm',
                'label' => 'M',
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

        $result = self::getContainer()->get(GetAttributeOptionsByCodeQuery::class)->execute('clothing_size', ['xs', 'm', 'xl'], 'jp_JP');
        $this->assertEquals([
            [
                'code' => 'xs',
                'label' => '[xs]',
            ],
            [
                'code' => 'm',
                'label' => '[m]',
            ],
            [
                'code' => 'xl',
                'label' => '[xl]',
            ],
        ], $result);
    }
}
