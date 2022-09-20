<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

class DisableCatalogOnAttributeOptionRemovalTaskletTest extends IntegrationTestCase
{
    private ?QueryBus $queryBus;
    private ?JobLauncher $jobLauncher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->queryBus = self::getContainer()->get(QueryBus::class);
        $this->jobLauncher = self::getContainer()->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobLauncher->flushJobQueue();
    }

    public function testItDisablesCatalogOnAttributeOptionRemoval(): void
    {
        $this->getAuthenticatedInternalApiClient();
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['red'],
        ]);
        $this->createProduct('tshirt-red', [
            new SetSimpleSelectValue('color', null, null, 'red')
        ]);

        $idCatalog = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createUser('shopifi');
        $this->createCatalog($idCatalog, 'Store US', 'shopifi');
        $this->enableCatalog($idCatalog);

        $this->setCatalogProductSelection($idCatalog, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->removeAttributeOption('color.red');
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertCatalogIsDisabled($idCatalog);
    }

    private function assertCatalogIsDisabled(string $id): void
    {
        /** @var ?Catalog $catalog */
        $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        $this->assertNotNull($catalog);
        $this->assertFalse($catalog->isEnabled());
    }

    private function removeAttributeOption(string $code): void
    {
        $attributeOption = self::getContainer()->get('pim_catalog.repository.attribute_option')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.remover.attribute_option')->remove($attributeOption);
    }
}
