<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Test attribute filters with the EMPTY operator for product and product models
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyFilterWithProductsAndProductModelsIntegration extends TestCase
{
    public function testEmptyOperatorForDateFilter()
    {
        $this->loadFixtures(
            'a_date',
            ['data' => '2020-05-16', 'scope' => null, 'locale' => null],
            new SetDateValue('a_date', null, null, new \DateTime('2020-05-16'))
        );
        $this->assert('a_date');
    }

    public function testEmptyOperatorForMediaFilter()
    {
        $this->loadFixtures(
            'a_file',
            ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.txt')), 'scope' => null, 'locale' => null],
            new SetFileValue('a_file', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.txt')))
        );
        $this->assert('a_file');
    }

    public function testEmptyOperatorForMetricFilter()
    {
        $this->loadFixtures(
            'a_metric',
            ['data' => ['amount' => 2, 'unit' => 'KILOWATT'], 'scope' => null, 'locale' => null],
            new SetMeasurementValue('a_metric', null, null, 2, 'KILOWATT')
        );
        $this->assert('a_metric');
    }

    public function testEmptyOperatorForNumberFilter()
    {
        $this->loadFixtures(
            'a_number_float',
            ['data' => 25, 'scope' => null, 'locale' => null],
            new SetNumberValue('a_number_float', null, null, '25')
        );
        $this->assert('a_number_float');
    }

    public function testEmptyOperatorForOptionFilter()
    {
        $this->loadFixtures(
            'a_simple_select',
            ['data' => 'optionA', 'scope' => null, 'locale' => null],
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')
        );
        $this->assert('a_simple_select');
    }

    public function testEmptyOperatorForPriceFilter()
    {
        $this->loadFixtures(
            'a_price',
            ['data' => [['amount' => 100, 'currency' => 'USD']], 'scope' => null, 'locale' => null],
            new SetPriceCollectionValue('a_price', null, null, [
                new PriceValue(100, 'USD')
            ])
        );
        $this->assert('a_price');
    }

    public function testEmptyOperatorForReferenceDataFilter()
    {
        $this->loadFixtures(
            'a_ref_data_simple_select',
            ['data' => 'red', 'scope' => null, 'locale' => null],
            new SetSimpleReferenceEntityValue('a_ref_data_simple_select', null, null, 'red')
        );
        $this->assert('a_ref_data_simple_select');
    }

    public function testEmptyOperatorForTextareaFilter()
    {
        $this->loadFixtures(
            'a_text_area',
            ['data' => 'Lorem ipsum dolor sit amet', 'scope' => null, 'locale' => null],
            new SetTextareaValue('a_text_area', null, null, 'Lorem ipsum dolor sit amet')
        );
        $this->assert('a_text_area');
    }

    public function testEmptyOperatorForTextFilter()
    {
        $this->loadFixtures(
            'a_text',
            ['data' => 'Foobar', 'scope' => null, 'locale' => null],
            new SetTextValue('a_text', null, null, 'Foobar')
        );
        $this->assert('a_text');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assert(string $attributeCode)
    {
        $pqb = $this->get('pim_catalog.query.product_and_product_model_query_builder_factory')->create();
        $pqb->addFilter($attributeCode, Operators::IS_EMPTY, null);
        $results = $pqb->execute();
        $identifiers = [];
        foreach ($results as $entity) {
            $identifiers[] = $entity instanceof ProductModelInterface ? $entity->getCode() : $entity->getIdentifier();
        }
        Assert::assertEqualsCanonicalizing(
            ['pm_1_empty', 'variant_1_empty', 'variant_3_empty', 'simple_product_empty'],
            $identifiers
        );
    }

    /**
     * Creates
     * - a family with the given attribute code
     * - a family variant with the given attribute at root level, and another one with the attribute at variant level
     * - foreach family variant, product models and variant products with empty or filled value for the given attribute
     * - 2 simple products, one with empty value, one with non empty value
     * - a simple product without family
     */
    private function loadFixtures(string $attributeCode, array $nonEmptyData, UserIntent $userIntent)
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => [$attributeCode, 'sku', 'a_yes_no', 'a_number_float_negative']
        ]);
        $this->createFamilyVariant([
            'code' => 'attribute_at_common_level',
            'family' => 'a_family',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_yes_no'],
                    'attributes' => ['sku', 'a_yes_no']
                ]
            ]
        ]);
        $this->createProductModel([
            'code' => 'pm_1_empty',
            'family_variant' => 'attribute_at_common_level',
        ]);
        $this->createProduct('variant_1_empty', [
            new ChangeParent('pm_1_empty'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);
        $this->createProductModel([
            'code' => 'pm_2_filled',
            'family_variant' => 'attribute_at_common_level',
            'values' => [
                $attributeCode => [$nonEmptyData],
            ]
        ]);
        $this->createProduct('variant_2_filled', [
            new ChangeParent('pm_2_filled'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);

        $this->createFamilyVariant([
            'code' => 'attribute_at_variant_level',
            'family' => 'a_family',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_yes_no'],
                    'attributes' => ['sku', 'a_yes_no', $attributeCode]
                ]
            ]
        ]);

        $this->createProductModel([
            'code' => 'pm_3',
            'family_variant' => 'attribute_at_variant_level',
        ]);

        $this->createProduct('variant_3_empty', [
            new ChangeParent('pm_3'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);
        $this->createProduct('variant_3_filled', \array_merge(
            [$userIntent],
            [
                new ChangeParent('pm_3'),
                new SetBooleanValue('a_yes_no', null, null, false),
            ]
        ));

        $this->createProduct('simple_product_empty', [
            new SetFamily('a_family')
        ]);
        $this->createProduct('simple_product_filled', \array_merge(
            [new SetFamily('a_family')],
            [$userIntent]
        ));
        $this->createProduct('simple_product_without_family', []);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        Assert::assertEmpty($this->get('validator')->validate($family));
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant($data): void
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        Assert::assertEmpty($this->get('validator')->validate($familyVariant));
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
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
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        Assert::assertEmpty($this->get('pim_catalog.validator.product_model')->validate($productModel));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }
}
