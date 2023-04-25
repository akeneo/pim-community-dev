<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateIndexVersionIntegration extends TestCase
{
    private string $productAndProductModelIndexName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productAndProductModelIndexName = $this->getParameter('product_and_product_model_index_name');
    }

    public function testCommandResetsAllIndexes()
    {
        $productIndexNameBeforeMigration = $this->getProductAndProductModelIndexName();
        $numberOfDocumentBeforeMigration = $this->getNumberOfIndexedProductAndProductModel();
        $firstDocumentBeforeMigration = $this->getFirstProductAndProductModel();

        $this->assertCommandSuccess($this->runUpdateIndexVersionCommand($this->productAndProductModelIndexName));

        $productIndexNameAfterMigration = $this->getProductAndProductModelIndexName();
        $numberOfDocumentAfterMigration = $this->getNumberOfIndexedProductAndProductModel();
        $firstDocumentAfterMigration = $this->getFirstProductAndProductModel();

        $this->assertNotEquals($productIndexNameBeforeMigration, $productIndexNameAfterMigration);
        $this->assertEquals($numberOfDocumentBeforeMigration, $numberOfDocumentAfterMigration);
        $this->assertEquals($firstDocumentBeforeMigration, $firstDocumentAfterMigration);
    }

    public function testCommandFailedItTheIndexGivenHaveAnAlias()
    {
        $this->assertCommandFailedWithMessage(
            $this->runUpdateIndexVersionCommand($this->getProductAndProductModelIndexName()),
            "Index with alias is not allowed, you should give the alias instead"
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function runUpdateIndexVersionCommand(string $indexName): CommandTester
    {
        $application = new Application(self::$kernel);
        $command = $application->find('akeneo:elasticsearch:update-index-version');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'indices' => [$indexName]
        ], ['capture_stderr_separately' => true]);

        return $commandTester;
    }

    private function getClient(): Client
    {
        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts([$this->getParameter('index_hosts')]);

        return $clientBuilder->build();
    }

    private function getProductAndProductModelIndexName(): string
    {
        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts([$this->getParameter('index_hosts')]);
        $client = $clientBuilder->build();

        $aliasConfiguration = $client->indices()->get(['index' => $this->productAndProductModelIndexName]);
        $indexNames = array_keys($aliasConfiguration);

        if (count($indexNames) !== 1) {
            throw new \Exception('There is multiple index behind the index alias or there is no alias behind the alias');
        }

        return $indexNames[0];
    }

    private function getNumberOfIndexedProductAndProductModel(): int
    {
        $response = $this->getClient()->count(['index' => $this->productAndProductModelIndexName]);

        return $response['count'];
    }

    private function getFirstProductAndProductModel()
    {
        $response = $this->getClient()->search(['index' => $this->productAndProductModelIndexName, "size" => 1]);

        return $response['hits']['hits'][0]['_source'];
    }

    private function assertCommandSuccess(CommandTester $commandTester): void
    {
        $exitCode = $commandTester->getStatusCode();

        $this->assertSame(0, $exitCode);
    }

    private function assertCommandFailedWithMessage(CommandTester $commandTester, string $errorMessage): void
    {
        $exitCode = $commandTester->getStatusCode();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString($errorMessage, $commandTester->getErrorOutput());
    }
}
