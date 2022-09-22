<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Job;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

class DisableCatalogsOnCategoryRemovalTaskletTest extends IntegrationTestCase
{
    private ?JobLauncher $jobLauncher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->jobLauncher = self::getContainer()->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobLauncher->flushJobQueue();
    }

    public function testItDisablesCatalogOnCategoryRemoval(): void
    {
        $this->getAuthenticatedInternalApiClient();
        $this->createCategory([
            'code' => 'tshirt',
            'labels' => ['en_US' => 'T-shirt'],
        ]);
        $this->createCategory([
            'code' => 'scanner',
            'labels' => ['en_US' => 'Scanner'],
        ]);

        $idCatalogUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idCatalogFR = 'b79b09a3-cb4c-45f8-a086-4f70cc17f521';
        $this->createUser('shopifi');
        $this->createUser('magenta');
        $this->createCatalog($idCatalogUS, 'Store US', 'shopifi');
        $this->createCatalog($idCatalogFR, 'Store FR', 'magenta');
        $this->enableCatalog($idCatalogUS);
        $this->enableCatalog($idCatalogFR);

        $this->setCatalogProductSelection($idCatalogUS, [
            [
                'field' => 'category',
                'operator' => Operator::IN_LIST,
                'value' => ['tshirt'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($idCatalogFR, [
            [
                'field' => 'category',
                'operator' => Operator::IN_LIST,
                'value' => ['scanner'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->removeCategory('tshirt');
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->assertCatalogIsDisabled($idCatalogUS);
        $this->assertCatalogIsEnabled($idCatalogFR);
    }

    private function removeCategory(string $code): void
    {
        $category = self::getContainer()->get('pim_catalog.repository.category')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.remover.category')->remove($category);
    }
}
