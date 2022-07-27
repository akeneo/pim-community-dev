<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\Command;

use Akeneo\Pim\Enrichment\Bundle\Command\CleanRemovedProductsCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;

class CleanRemovedProductsCommandEndToEnd extends TestCase
{
    protected Command $command;
    protected Client $esProductClient;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function setUp(): void
    {
        parent::setUp();
        //Create a product
        /*$command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'existing_product_in_db',
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $productUuid = $this->getProductUuidFromIdentifier('existing_product_in_db');
        $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection')->fromProductUuids([$productUuid]);*/
        //$this->command = $this->application->find('pim:product:clean-removed-products');
        //$this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        //$this->get('pim:product:clean-removed-products')->load();

        //$this->runResetIndexesCommand();
    }

    private function runResetIndexesCommand(): void
    {
        $commandLauncher = new CommandLauncher(static::$kernel);
        $exitCode = $commandLauncher->execute('pim:product:clean-removed-products', null, ['inputs' => ['yes']]);
        $this->assertSame(0, $exitCode);
    }

    public function test_getAncestorsFromProductsIds_result(): void
    {
        //$commandTester = new CommandTester($this->command);
        //$commandTester->execute();
        //$commandDiff = $this->get('pim:product:clean-removed-products');

        //$commandDiff->getAncestorsFromProductsIds();

    }

    /*public function testDeleteAProduct()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();

        $this->assertCount(7, $this->get('pim_catalog.repository.product')->findAll());

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductIdentifier('foo');
        $client->request('DELETE', 'api/rest/v1/products/foo');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(6, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('foo'));

        $this->assertEventCount(1, ProductRemoved::class);
    }

    public function testNotFoundAProduct()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', 'api/rest/v1/products/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('The not_found product does not exist in your PIM or you do not have permission to access it.', $content['message']);
    }

    public function testAccessDeniedWhenDeletingProductWithoutTheAcl()
    {
        $this->createAdminUser();
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_remove');

        $client->request('DELETE', 'api/rest/v1/products/foo');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }*/

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }

    private function getProductUuidFromIdentifier(string $productIdentifier): UuidInterface
    {
        return Uuid::fromString($this->get('database_connection')->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?', [$productIdentifier]
        ));
    }
}
