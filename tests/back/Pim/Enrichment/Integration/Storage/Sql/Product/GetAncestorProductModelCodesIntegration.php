<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class GetAncestorProductModelCodesIntegration extends TestCase
{
    public function test_that_it_returns_an_empty_array_for_simple_products()
    {
        Assert::assertSame(
            [],
            $this->get('akeneo.pim.enrichment.product.query.get_ancestor_product_model_codes')
                ->fromProductIdentifiers(['simple_product', 'another_product'])
        );
    }

    public function test_that_it_returns_ancestor_codes_of_variant_products()
    {
        Assert::assertEqualsCanonicalizing(
            ['root_A1', 'subpm_A1_optionA', 'root_A2'],
            $this->get('akeneo.pim.enrichment.product.query.get_ancestor_product_model_codes')
                 ->fromProductIdentifiers(['simple_product', 'variant_A1_A_no', 'variant_A1_A_yes', 'variant_A2_B_no'])
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProductModel(['code' => 'root_A1', 'family_variant' => 'familyVariantA1']);
        $this->createProductModel([
            'code' => 'subpm_A1_optionA',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root_A1',
            'values' => [
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
            ],
        ]);
        $this->createProduct('variant_A1_A_yes', [
            new ChangeParent('subpm_A1_optionA'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);
        $this->createProduct('variant_A1_A_no', [
            new ChangeParent('subpm_A1_optionA'),
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);
        $this->createProductModel([
            'code' => 'subpm_A1_optionB',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root_A1',
            'values' => [
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionB']]
            ]
        ]);
        $this->createProduct('variant_A1_B_yes', [
            new ChangeParent('subpm_A1_optionB'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);
        $this->createProduct('variant_A1_B_no', [
            new ChangeParent('subpm_A1_optionB'),
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);
        $this->createProductModel(['code' => 'root_A2', 'family_variant' => 'familyVariantA2']);
        $this->createProduct('variant_A2_A_yes', [
            new ChangeParent('root_A2'),
            new SetBooleanValue('a_yes_no', null, null, true),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')
        ]);
        $this->createProduct('variant_A2_B_no', [
            new ChangeParent('root_A2'),
            new SetBooleanValue('a_yes_no', null, null, false),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
        ]);
        $this->createProduct('simple_product', [new SetFamily('familyA3')]);
        $this->createProduct('another_product', [new SetFamily('familyA2')]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents): void
    {
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
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
