<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadProductAndProductModelIntegration extends TestCase
{
    public function test_product_is_not_dirty_after_fetching_it_from_database()
    {
        $this->createProduct(
            'baz',
            [
                new SetFamily('familyA'),
                new SetBooleanValue('a_yes_no', null, null, true),
                new SetTextValue('a_text', null, null, 'Lorem ipsum dolor sit amet'),
                new SetGroups(['groupA']),
                new SetCategories(['categoryA']),
                new AssociateProducts('X_SELL', ['foo']),
                new AssociateProductModels('X_SELL', ['bar']),
                new AssociateGroups('X_SELL', ['groupB']),
                new AssociateProducts('TWOWAY', ['foo']),
                new AssociateProductModels('TWOWAY', ['bar']),
                new AssociateQuantifiedProducts('QUANTIFIED', [new QuantifiedEntity('foo', 2)]),
                new AssociateQuantifiedProductModels('QUANTIFIED', [
                    new QuantifiedEntity('bar', 5)
                ])
            ]
        );

        $baz = $this->get('pim_catalog.repository.product')->findOneByIdentifier('baz');
        Assert::assertFalse($baz->isDirty(), 'The product should not be dirty after loading it from the database');
    }

    public function test_product_model_is_not_dirty_after_fetching_it_from_database()
    {
        $this->createProductModel(
            [
                'code' => 'baz',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_number_integer' => [['locale' => null, 'scope' => null, 'data' => 42]],
                    'a_multi_select' => [['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']]],
                ],
                'categories' => ['categoryA'],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['foo'],
                        'product_models' => ['bar'],
                        'groups' => ['groupB'],
                    ],
                    'TWOWAY' => [
                        'products' => ['foo'],
                        'product_models' => ['bar'],
                    ]
                ],
                'quantified_associations' => [
                    'QUANTIFIED' => [
                        'products' => [
                            [
                                'identifier' => 'foo',
                                'quantity' => 2,
                            ],
                        ],
                        'product_models' => [
                            [
                                'identifier' => 'bar',
                                'quantity' => 5,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $baz = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('baz');
        Assert::assertFalse($baz->isDirty(), 'The product model should not be dirty after loading it from the database');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // create a 2-way association type
        $twoWayAssociationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $twoWayAssociationType,
            [
                'code' => 'TWOWAY',
                'is_two_way' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($twoWayAssociationType);

        // create a quantified association type
        $quantifiedAssociationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $quantifiedAssociationType,
            [
                'code' => 'QUANTIFIED',
                'is_quantified' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($quantifiedAssociationType);

        $this->createProduct('foo', []);
        $this->createProductModel(
            [
                'code' => 'bar',
                'family_variant' => 'familyVariantA1',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
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
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is not valid: %s', (string)$violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
