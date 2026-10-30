<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Associations aren't part of the Elasticsearch documents, so the reindexing of the reciprocal
 * entity is made observable by deleting its document before saving the owner: the owner is the
 * only entity explicitly saved, so the document can only come back if the two-way association
 * update queued the reciprocal for indexing.
 */
class TwoWayAssociationIndexingIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_when_the_owner_removes_a_two_way_association(): void
    {
        $this->createProduct('owner', [new AssociateProducts('COMPATIBILITY', ['reciprocal'])]);

        $reciprocalDocumentId = $this->productDocumentId('reciprocal');
        $this->assertDocumentExists($reciprocalDocumentId);

        $this->deleteDocument($reciprocalDocumentId);

        $this->createProduct('owner', [new DissociateProducts('COMPATIBILITY', ['reciprocal'])]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_when_the_owner_adds_a_two_way_association(): void
    {
        $reciprocalDocumentId = $this->productDocumentId('reciprocal');
        $this->deleteDocument($reciprocalDocumentId);

        $this->createProduct('owner', [new AssociateProducts('COMPATIBILITY', ['reciprocal'])]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_model_when_the_owner_product_removes_a_two_way_association(): void
    {
        $this->createProduct('owner', [new AssociateProductModels('COMPATIBILITY', ['reciprocal_pm'])]);

        $reciprocalDocumentId = $this->productModelDocumentId('reciprocal_pm');
        $this->assertDocumentExists($reciprocalDocumentId);

        $this->deleteDocument($reciprocalDocumentId);

        $this->createProduct('owner', [new DissociateProductModels('COMPATIBILITY', ['reciprocal_pm'])]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_model_when_the_owner_product_adds_a_two_way_association(): void
    {
        $reciprocalDocumentId = $this->productModelDocumentId('reciprocal_pm');
        $this->deleteDocument($reciprocalDocumentId);

        $this->createProduct('owner', [new AssociateProductModels('COMPATIBILITY', ['reciprocal_pm'])]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_when_the_owner_product_model_removes_a_two_way_association(): void
    {
        $this->upsertProductModel('owner_pm', ['products' => ['reciprocal'], 'product_models' => []]);

        $reciprocalDocumentId = $this->productDocumentId('reciprocal');
        $this->assertDocumentExists($reciprocalDocumentId);

        $this->deleteDocument($reciprocalDocumentId);

        $this->upsertProductModel('owner_pm', ['products' => [], 'product_models' => []]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_when_the_owner_product_model_adds_a_two_way_association(): void
    {
        $this->upsertProductModel('owner_pm', ['products' => [], 'product_models' => []]);

        $reciprocalDocumentId = $this->productDocumentId('reciprocal');
        $this->deleteDocument($reciprocalDocumentId);

        $this->upsertProductModel('owner_pm', ['products' => ['reciprocal'], 'product_models' => []]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_model_when_the_owner_product_model_removes_a_two_way_association(): void
    {
        $this->upsertProductModel('owner_pm', ['products' => [], 'product_models' => ['reciprocal_pm']]);

        $reciprocalDocumentId = $this->productModelDocumentId('reciprocal_pm');
        $this->assertDocumentExists($reciprocalDocumentId);

        $this->deleteDocument($reciprocalDocumentId);

        $this->upsertProductModel('owner_pm', ['products' => [], 'product_models' => []]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    /**
     * @test
     */
    public function it_reindexes_the_reciprocal_product_model_when_the_owner_product_model_adds_a_two_way_association(): void
    {
        $this->upsertProductModel('owner_pm', ['products' => [], 'product_models' => []]);

        $reciprocalDocumentId = $this->productModelDocumentId('reciprocal_pm');
        $this->deleteDocument($reciprocalDocumentId);

        $this->upsertProductModel('owner_pm', ['products' => [], 'product_models' => ['reciprocal_pm']]);

        $this->assertDocumentExists($reciprocalDocumentId);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $associationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $associationType,
            [
                'code' => 'COMPATIBILITY',
                'is_two_way' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($associationType);

        $this->createProduct('reciprocal', []);
        $this->upsertProductModel('reciprocal_pm', []);
    }

    private function productDocumentId(string $identifier): string
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        Assert::assertNotNull($product, \sprintf('Product "%s" does not exist', $identifier));

        return \sprintf('product_%s', $product->getUuid()->toString());
    }

    private function productModelDocumentId(string $code): string
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code);
        Assert::assertNotNull($productModel, \sprintf('Product model "%s" does not exist', $code));

        return \sprintf('product_model_%d', $productModel->getId());
    }

    private function deleteDocument(string $documentId): void
    {
        $client = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $client->delete($documentId);
        $client->refreshIndex();
    }

    private function assertDocumentExists(string $documentId): void
    {
        $client = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $client->refreshIndex();

        $result = $client->search([
            'query' => ['term' => ['_id' => $documentId]],
        ]);

        Assert::assertSame(
            1,
            $result['hits']['total']['value'],
            \sprintf('Expected the Elasticsearch document "%s" to exist', $documentId)
        );
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->clearUnitOfWork();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    /**
     * Creates the product model if it does not exist yet, then sets its COMPATIBILITY associations
     * (left untouched when $compatibilityAssociations is empty) and saves it.
     */
    private function upsertProductModel(string $code, array $compatibilityAssociations): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code);
        $data = [];
        if (null === $productModel) {
            $productModel = $this->get('pim_catalog.factory.product_model')->create();
            $data = ['code' => $code, 'family_variant' => 'familyVariantA1'];
        }
        if (!empty($compatibilityAssociations)) {
            $data['associations'] = ['COMPATIBILITY' => $compatibilityAssociations];
        }

        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is invalid: %s', (string) $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->clearUnitOfWork();

        return $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code);
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $id = $this->get('database_connection')->executeQuery($query, ['username' => $username])->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    private function clearUnitOfWork(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
