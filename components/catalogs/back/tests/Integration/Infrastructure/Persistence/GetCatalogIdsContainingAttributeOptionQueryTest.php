<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\GetCatalogIdsContainingAttributeOptionQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\GetCatalogIdsContainingAttributeOptionQuery
 */
class GetCatalogIdsContainingAttributeOptionQueryTest extends IntegrationTestCase
{
    private ?GetCatalogIdsContainingAttributeOptionQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogIdsContainingAttributeOptionQuery::class);
    }

    public function testItGetsCatalogsByAttributeOption(): void
    {
        $this->createUser('shopifi');
        $uuidUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $uuidFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $uuidUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';

        $this->createCatalog($uuidUS, 'Store US', 'shopifi');
        $this->createCatalog($uuidFR, 'Store FR', 'shopifi');
        $this->createCatalog($uuidUK, 'Store UK', 'shopifi');

        $this->enableCatalog($uuidUS);
        $this->enableCatalog($uuidFR);
        $this->enableCatalog($uuidUK);

        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['red', 'green', 'blue'],
        ]);

        $this->setCatalogProductSelection($uuidUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red', 'green'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($uuidFR, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $resultRed = $this->query->execute('color', 'red');
        $this->assertEquals([$uuidUS, $uuidFR], $resultRed);

        $resultGreen = $this->query->execute('color', 'green');
        $this->assertEquals([$uuidUS], $resultGreen);

        $resultBlue = $this->query->execute('color', 'blue');
        $this->assertEquals([], $resultBlue);
    }
}
