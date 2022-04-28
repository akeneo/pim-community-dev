<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class FindNonExistingProductIdentifiersQueryIntegration extends TestCase
{
    /** @var FindNonExistingProductIdentifiersQueryInterface */
    private $findNonExistingProductIdentifiersQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findNonExistingProductIdentifiersQuery = $this->get(
            'akeneo.pim.enrichment.product.query.find_non_existing_product_identifiers_query'
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_return_nothing_when_nothing_passed()
    {
        self::assertEquals([], $this->findNonExistingProductIdentifiersQuery->execute([]));
    }

    /**
     * @test
     */
    public function it_returns_the_product_identifiers_that_does_not_exists()
    {
        $existingProductIdentifiers = [
            'product_1',
            'product_2',
            'product_3',
            'product_4',
            'product_5',
        ];

        foreach ($existingProductIdentifiers as $productIdentifier) {
            $this->createProduct($productIdentifier);
        }

        $lookupProductIdentifiers = [
            'product_1',
            'product_2',
            'product_3',
            'product_does_not_exists',
        ];

        $actualNonExistingProductIdentifiers = $this->findNonExistingProductIdentifiersQuery->execute(
            $lookupProductIdentifiers
        );
        $expectedNonExistingProductIdentifiers = [
            'product_does_not_exists',
        ];

        self::assertEquals(
            $actualNonExistingProductIdentifiers,
            $expectedNonExistingProductIdentifiers
        );
    }

    private function createProduct(string $productIdentifier): void
    {
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $productIdentifier,
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }
}
