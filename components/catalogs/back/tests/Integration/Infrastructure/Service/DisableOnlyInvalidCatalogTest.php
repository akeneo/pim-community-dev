<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Service;

use Akeneo\Catalogs\Application\Service\DisableOnlyInvalidCatalogInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Service\DisableOnlyInvalidCatalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

class DisableOnlyInvalidCatalogTest extends IntegrationTestCase
{
    private ?DisableOnlyInvalidCatalogInterface $disableOnlyInvalidCatalogQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->disableOnlyInvalidCatalogQuery = self::getContainer()->get(DisableOnlyInvalidCatalog::class);
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDoesNothingIfCatalogIsValid(): void
    {
        $this->getAuthenticatedInternalApiClient();
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
        $isCatalogDisabled = $this->disableOnlyInvalidCatalogQuery->disable($this->getCatalog($catalogIdUS));

        $this->assertFalse($isCatalogDisabled);
        $this->assertCatalogIsEnabled($catalogIdUS);
    }

    public function testItDisablesAnInvalidCatalog(): void
    {
        $this->getAuthenticatedInternalApiClient();
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

        $isCatalogDisabled = $this->disableOnlyInvalidCatalogQuery->disable($this->getCatalog($catalogIdUS));

        $this->assertTrue($isCatalogDisabled);
        $this->assertCatalogIsDisabled($catalogIdUS);
    }

    private function assertCatalogIsDisabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertFalse($catalog->isEnabled());
    }

    private function assertCatalogIsEnabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertTrue($catalog->isEnabled());
    }
}
