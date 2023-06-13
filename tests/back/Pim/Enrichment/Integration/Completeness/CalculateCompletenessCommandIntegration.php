<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

class CalculateCompletenessCommandIntegration extends TestCase
{
    private $productUuids = [];
    private $productModelIds = [];

    public function test_that_it_computes_completeness_and_reindexes_all_products_and_their_ancestors()
    {
        $commandLauncher = new CommandLauncher(static::$kernel);
        $commandLauncher->execute('pim:completeness:calculate');

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $identifiers = ['simple_product', 'variant_A_yes', 'variant_A_no'];
        $this->assertCompletenessWasComputedForProducts($identifiers);
        foreach ($identifiers as $identifier) {
            Assert::assertTrue($this->isProductIndexed($this->productUuids[$identifier]));
        }
        foreach (['sub_pm_A', 'root_pm'] as $productModelCode) {
            Assert::assertTrue($this->isProductModelIndexed($this->productModelIds[$productModelCode]));
        }
        // sub_pm_B has no variant product, the command should not reindex it
        Assert::assertFalse($this->isProductModelIndexed($this->productModelIds['sub_pm_B']));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProductModel(
            [
                'code' => 'root_pm',
                'family_variant' => 'familyVariantA1',
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_A',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_B',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionB']],
                ],
            ]
        );
        $this->createProduct(
            'variant_A_yes',
            [
                new ChangeParent('sub_pm_A'),
                new SetBooleanValue('a_yes_no', null, null, true)
            ]
        );
        $this->createProduct(
            'variant_A_no',
            [
                new ChangeParent('sub_pm_A'),
                new SetBooleanValue('a_yes_no', null, null, false)
            ]
        );
        $this->createProduct(
            'simple_product',
            [
                new SetFamily('familyA3'),
                new SetBooleanValue('a_yes_no', null, null, true)
            ]
        );

        $this->purgeCompletenessAndResetIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function purgeCompletenessAndResetIndex(): void
    {
        $this->get('database_connection')->executeUpdate('DELETE c.* from pim_catalog_completeness c');
        $this->get('database_connection')->executeUpdate('DELETE c.* from pim_catalog_product_completeness c');
        $client = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $client->refreshIndex();
        $client->bulkDelete(
            array_map(
                function (int $productModelId): string {
                    return sprintf('product_model_%d', $productModelId);
                },
                $this->productModelIds
            )
        );
        $client->bulkDelete(
            array_map(
                function (UuidInterface $productUuid): string {
                    return sprintf('product_%s', $productUuid->toString());
                },
                $this->productUuids
            )
        );
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);

        $this->productUuids[$identifier] = $product->getUuid();
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->productModelIds[$productModel->getCode()] = $productModel->getId();
    }

    private function assertCompletenessWasComputedForProducts(array $identifiers): void
    {
        foreach ($identifiers as $identifier) {
            $uuid = $this->productUuids[$identifier];
            $completenesses = $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
                ->fromProductUuid($uuid);
            Assert::assertCount(6, $completenesses); // 3 channels * 2 locales
        }
    }

    private function isProductIndexed(UuidInterface $productUuid): bool
    {
        return null !== $this->get('akeneo_elasticsearch.client.product_and_product_model')
                             ->get(sprintf('product_%s', $productUuid->toString()));
    }

    private function isProductModelIndexed(int $productModelId): bool
    {
        try {
            $this->get('akeneo_elasticsearch.client.product_and_product_model')->get(
                sprintf('product_model_%d', $productModelId)
            );
        } catch (Missing404Exception $e) {
            return false;
        }

        return true;
    }
}
