<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

class DisableCatalogsOnAttributeOptionRemovalTaskletTest extends IntegrationTestCase
{
    private ?JobLauncher $jobLauncher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->jobLauncher = self::getContainer()->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobLauncher->flushJobQueue();
    }

    public function testItDisablesCatalogOnAttributeOptionRemoval(): void
    {
        $this->getAuthenticatedInternalApiClient();
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_multiselect',
            'options' => ['red', 'blue'],
        ]);

        $idCatalogUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idCatalogFR = 'b79b09a3-cb4c-45f8-a086-4f70cc17f521';
        $this->createUser('shopifi');
        $this->createUser('magento');
        $this->createCatalog($idCatalogUS, 'Store US', 'shopifi');
        $this->createCatalog($idCatalogFR, 'Store FR', 'magento');
        $this->enableCatalog($idCatalogUS);
        $this->enableCatalog($idCatalogFR);

        $this->setCatalogProductSelection($idCatalogUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($idCatalogFR, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['blue'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->removeAttributeOption('color.red');
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertCatalogIsDisabled($idCatalogUS);
        $this->assertCatalogIsEnabled($idCatalogFR);
    }

    private function removeAttributeOption(string $code): void
    {
        $attributeOption = self::getContainer()->get('pim_catalog.repository.attribute_option')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.remover.attribute_option')->remove($attributeOption);
    }
}
