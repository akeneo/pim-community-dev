<?php

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\GetCatalogsByAttributeOptionQuery;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class GetCatalogsByAttributeOptionQueryTest extends IntegrationTestCase
{
    private ?GetCatalogsByAttributeOptionQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogsByAttributeOptionQuery::class);
    }

    public function testItGetsCatalogsByAttributeOption(): void
    {
        $this->createUser('owner');
        $idUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $idUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';

        $this->createCatalog($idUS, 'Store US', 'owner');
        $this->createCatalog($idFR, 'Store FR', 'owner');
        $this->createCatalog($idUK, 'Store UK', 'owner');

        $this->enableCatalog($idUS);
        $this->enableCatalog($idFR);
        $this->enableCatalog($idUK);

        $colorAttribute = $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => [],
        ]);

        $redAttributeOption = $this->createAttributeOption('red', $colorAttribute, 0);
        $greenAttributeOption = $this->createAttributeOption('green', $colorAttribute, 1);
        $blueAttributeOption = $this->createAttributeOption('blue', $colorAttribute, 2);

        $this->setCatalogProductSelection($idUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red', 'green'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->setCatalogProductSelection($idFR, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $resultRed = $this->query->execute($redAttributeOption);
        $expectedRed = [
            new Catalog($idUS, 'Store US', 'owner', true),
            new Catalog($idFR, 'Store FR', 'owner', true),
        ];
        $this->assertEquals($expectedRed, $resultRed);

        $resultGreen = $this->query->execute($greenAttributeOption);
        $expectedGreen = [
            new Catalog($idUS, 'Store US', 'owner', true),
        ];
        $this->assertEquals($expectedGreen, $resultGreen);

        $resultBlue = $this->query->execute($blueAttributeOption);
        $expectedBlue = [];
        $this->assertEquals($expectedBlue, $resultBlue);
    }
}
