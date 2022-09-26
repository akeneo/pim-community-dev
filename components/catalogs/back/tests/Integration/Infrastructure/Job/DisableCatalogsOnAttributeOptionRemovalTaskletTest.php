<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableCatalogsOnAttributeOptionRemovalTaskletTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableExperimentalTestDatabase();
        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogOnAttributeOptionRemoval(): void
    {
        $this->getAuthenticatedInternalApiClient();
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_multiselect',
            'options' => ['red', 'blue'],
        ]);

        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $catalogIdFR = 'b79b09a3-cb4c-45f8-a086-4f70cc17f521';
        $this->createUser('shopifi');
        $this->createUser('magenta');
        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');
        $this->createCatalog($catalogIdFR, 'Store FR', 'magenta');
        $this->enableCatalog($catalogIdUS);
        $this->enableCatalog($catalogIdFR);

        $this->setCatalogProductSelection($catalogIdUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($catalogIdFR, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['blue'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->removeAttributeOption('color.red');

        $this->assertCatalogIsDisabled($catalogIdUS);
        $this->assertCatalogIsEnabled($catalogIdFR);
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
