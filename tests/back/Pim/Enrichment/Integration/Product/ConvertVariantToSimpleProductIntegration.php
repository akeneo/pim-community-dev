<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ConvertVariantToSimpleProductIntegration extends TestCase
{
    /** @var ProductInterface */
    private $product;

    /** @test */
    public function it_converts_a_variant_product_into_a_simple_one(): void
    {
        Assert::assertTrue($this->product->isVariant());

        $initialValues = WriteValueCollection::fromCollection($this->product->getValues());
        $initialCategories = $this->product->getCategoryCodes();
        $initialAssociations = $this->get('pim_catalog.normalizer.standard.product.associations')
                                    ->normalize($this->product, 'standard');
        $expectedQuantifiedAssociations = [
            'quantified' => [
                'products' => [
                    [
                        'identifier' => 'other',
                        'quantity' => 10,
                    ],
                    [
                        'identifier' => 'random',
                        'quantity' => 2,
                    ],
                ],
                'product_models' => [
                    [
                        'identifier' => 'pm_1',
                        'quantity' => 5,
                    ],
                ],
            ],
        ];

        $this->convertToSimpleProduct($this->product);

        Assert::assertFalse($this->product->isVariant());
        Assert::assertNull($this->product->getFamilyVariant());
        $this->assertValues($this->product, $initialValues);
        $this->assertCategories($this->product, $initialCategories);
        $this->assertAssociations($this->product, $initialAssociations);
        $this->assertQuantifiedAssociations($this->product, $expectedQuantifiedAssociations);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $productFromDb = $this->get('pim_catalog.repository.product')->findOneByIdentifier('variant');
        $expectedQuantifiedAssociations = [
            'quantified' => [
                'products' => [
                    [
                        'uuid' => $this->getProductUuid('other')->toString(),
                        'identifier' => 'other',
                        'quantity' => 10,
                    ],
                    [
                        'uuid' => $this->getProductUuid('random')->toString(),
                        'identifier' => 'random',
                        'quantity' => 2,
                    ],
                ],
                'product_models' => [
                    [
                        'identifier' => 'pm_1',
                        'quantity' => 5,
                    ],
                ],
            ],
        ];

        Assert::assertFalse($productFromDb->isVariant());
        Assert::assertNull($productFromDb->getFamilyVariant());
        $this->assertValues($productFromDb, $initialValues);
        $this->assertCategories($productFromDb, $initialCategories);
        $this->assertAssociations($productFromDb, $initialAssociations);
        $this->assertQuantifiedAssociations($productFromDb, $expectedQuantifiedAssociations);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function loadFixtures()
    {
        $associationType = new AssociationType();
        $associationType->setCode('quantified');
        $associationType->setIsQuantified(true);
        $this->get('pim_catalog.saver.association_type')->save($associationType);

        $this->upsertProduct('random', [new SetFamily('familyA')]);
        $this->upsertProduct('other', [new SetFamily('familyA1')]);
        $this->createProductModel(['code' => 'pm_1', 'family_variant' => 'familyVariantA1']);
        $this->createProductModel(['code' => 'pm_2', 'family_variant' => 'familyVariantA2']);

        $this->createProductModel(
            [
                'code' => 'root',
                'family_variant' => 'familyVariantA2',
                'values' => [
                    'a_date' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => '2020-09-29',
                        ],
                    ],
                    'a_scopable_price' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => [
                                [
                                    'amount' => 10,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => [
                                [
                                    'amount' => 8,
                                    'currency' => 'USD',
                                ],
                            ],
                        ],
                    ],
                ],
                'categories' => ['categoryA1', 'categoryA2'],
                'associations' => [
                    'PACK' => [
                        'products' => ['random'],
                        'product_models' => ['pm_1'],
                        'groups' => ['groupA'],
                    ],
                ],
                'quantified_associations' => [
                    'quantified' => [
                        'product_models' => [
                            [
                                'identifier' => 'pm_1',
                                'quantity' => 5,
                            ]
                        ],
                        'products' => [
                            [
                                'identifier' => 'other',
                                'quantity' => 10,
                            ]
                        ]
                    ],
                ],
            ]
        );

        $this->product = $this->upsertProduct(
            'variant',
            [
                new ChangeParent('root'),
                new SetCategories(['categoryB']),
                new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
                new SetBooleanValue('a_yes_no', null, null, true),
                new SetTextValue('a_text', null, null, 'variant text'),
                new AssociateProducts('PACK', ['other']),
                new AssociateProductModels('UPSELL', ['pm_2']),
                new AssociateGroups('UPSELL', ['groupB']),
                new AssociateQuantifiedProducts('quantified', [new QuantifiedEntity('random', 2)])
            ]
        );
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    private function assertValues(ProductInterface $product, WriteValueCollection $expectedValues): void
    {
        $actualValues = $product->getValuesForVariation();

        Assert::assertSame($expectedValues->count(), $actualValues->count());
        foreach ($expectedValues as $expectedValue) {
            $actualValue = $actualValues->getSame($expectedValue);
            Assert::assertNotNull($actualValue);
            Assert::assertEquals($expectedValue, $actualValue);
        }
    }

    private function assertCategories(ProductInterface $product, array $expectedCategoryCodes): void
    {
        Assert::assertEqualsCanonicalizing($expectedCategoryCodes, $product->getCategoryCodes());
    }

    private function assertAssociations(ProductInterface $product, array $expectedAssociations): void
    {
        Assert::assertSame($product->getAssociations()->count(), count($expectedAssociations));
        foreach ($expectedAssociations as $associationTypeCode => $association) {
            Assert::assertTrue($product->hasAssociationForTypeCode($associationTypeCode));

            $actualAssociatedProductUuids = $product->getAssociatedProducts($associationTypeCode)->map(
                fn (ProductInterface $associatedProduct): string => $associatedProduct->getUuid()->toString()
            )->toArray();
            Assert::assertEqualsCanonicalizing($association['product_uuids'] ?? [], $actualAssociatedProductUuids);

            $actualAssociatedProductModelCodes = $product->getAssociatedProductModels($associationTypeCode)->map(
                fn (ProductModelInterface $associatedProductModel): string => $associatedProductModel->getCode()
            )->toArray();
            Assert::assertEqualsCanonicalizing(
                $association['product_models'] ?? [],
                $actualAssociatedProductModelCodes
            );

            $actualAssociatedGroupCodes = $product->getAssociatedGroups($associationTypeCode)->map(
                fn (GroupInterface $associatedGroup): string => $associatedGroup->getCode()
            )->toArray();
            Assert::assertEqualsCanonicalizing(
                $association['groups'] ?? [],
                $actualAssociatedGroupCodes
            );
        }
    }

    private function assertQuantifiedAssociations(ProductInterface $product, array $expectedQuantifiedAssociations): void
    {
        Assert::assertEqualsCanonicalizing($expectedQuantifiedAssociations, $product->getQuantifiedAssociations()->normalize());
    }

    private function convertToSimpleProduct(ProductInterface $product): void
    {
        if (!$product->isVariant()) {
            throw new \InvalidArgumentException('The "%s" product is already simple', $product->getIdentifier());
        }

        $this->product = $this->upsertProduct($product->getIdentifier(), [new ConvertToSimpleProduct()]);
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, sprintf('The product model is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function upsertProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }
}
