<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Validation\IsCatalogValidInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCatalogValidTest extends IntegrationTestCase
{
    private ?IsCatalogValidInterface $isCatalogValid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disableExperimentalTestDatabase();
        $this->isCatalogValid = self::getContainer()->get(IsCatalogValidInterface::class);
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsTrueIfCatalogIsValid(): void
    {
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_multiselect',
            'options' => ['red', 'blue'],
        ]);
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createUser('shopifi');
        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');
        $this->enableCatalog($catalogIdUS);
        $this->setCatalogProductSelection($catalogIdUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $isCatalogValid = ($this->isCatalogValid)($this->getCatalogDomain($catalogIdUS));
        $this->assertTrue($isCatalogValid);
    }

    public function testItReturnsFalseIfCatalogIsNotValid(): void
    {
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_multiselect',
            'options' => ['red', 'blue'],
        ]);
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createUser('shopifi');
        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');
        $this->enableCatalog($catalogIdUS);
        $this->setCatalogProductSelection($catalogIdUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->removeAttributeOption('color.red');

        $isCatalogValid = ($this->isCatalogValid)($this->getCatalogDomain($catalogIdUS));
        $this->assertFalse($isCatalogValid);
    }

    private function getCatalogDomain(string $id): Catalog
    {
        return self::getContainer()->get(GetCatalogQueryInterface::class)->execute($id);
    }
}
