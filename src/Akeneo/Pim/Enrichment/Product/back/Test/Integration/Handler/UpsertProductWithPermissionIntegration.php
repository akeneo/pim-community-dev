<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

final class UpsertProductWithPermissionIntegration extends EnrichmentProductTestCase
{
    private ProductRepositoryInterface $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        FeatureHelper::skipIntegrationTestWhenPermissionFeatureIsNotAvailable();
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->commandMessageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    /** @test */
    public function it_throws_an_exception_when_user_category_is_not_granted(): void
    {
        $this->createProduct('identifier', [new SetCategories(['print'])]);

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('You don\'t have access to products in any tree, please contact your administrator');

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('mary'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [
                new SetTextValue('a_text', null, null, 'foo'),
            ]
        );
        $this->commandMessageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_user_locale_is_not_granted(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('You don\'t have access to product data in any activated locale, please contact your administrator');

        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('mary'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetTextValue('name', null, 'en_GB', 'foo'),
        ]);
        $this->commandMessageBus->dispatch($command);
    }

    /** @test */
    public function it_creates_a_new_uncategorized_product(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('mary');
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('mary'), productIdentifier: ProductIdentifier::fromIdentifier('new_product'), userIntents: [
            new SetTextValue('name', null, null, 'foo'),
        ]);
        $this->commandMessageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('new_product');
        Assert::assertNotNull($product);
        Assert::assertSame('new_product', $product->getIdentifier());
        Assert::assertNotNull($product->getValue('name'));
    }

    /** @test */
    public function it_creates_a_categorized_product(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('betty'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['print'])]
        );
        $this->commandMessageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');

        Assert::assertNotNull($product);
        Assert::assertSame('identifier', $product->getIdentifier());
        Assert::assertEqualsCanonicalizing(['print'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_throws_an_exception_when_creating_a_product_with_non_viewable_category(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "suppliers" category does not exist');

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('betty'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['suppliers'])]
        );
        $this->commandMessageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_updating_a_product_with_non_viewable_category(): void
    {
        $this->createProduct('identifier', [new SetCategories(['print'])]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "suppliers" category does not exist');

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('betty'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['suppliers'])]
        );
        $this->commandMessageBus->dispatch($command);
    }

    /** @test */
    public function it_creates_a_product_without_owned_category(): void
    {
        $uuid = Uuid::uuid4();
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithUuid(
            userId: $this->getUserId('betty'),
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: [
                new SetIdentifierValue('sku', 'my_new_product'),
                new SetCategories(['sales'])
            ]
        );
        $this->commandMessageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $product = $this->productRepository->find($uuid);
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing(['sales'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_throws_an_exception_when_there_is_no_more_owned_category_after_update(): void
    {
        $this->createProduct('identifier', [new SetCategories(['print'])]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('You should at least keep your product in one category on which you have an own permission');

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('betty'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['sales'])] // betty can view 'sales' category, but is not owner.
        );
        $this->commandMessageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_there_is_no_more_owned_category_after_removing_category(): void
    {
        $this->createProduct('identifier', [new SetCategories(['print', 'sales'])]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('You should at least keep your product in one category on which you have an own permission');

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('betty'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new RemoveCategories(['print'])]
        );
        $this->commandMessageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_user_is_not_owner(): void
    {
        $this->createProduct('my_product', [new SetCategories(['sales'])]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage("You don't have access to products in any tree, please contact your administrator");

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('betty'),
            productIdentifier: ProductIdentifier::fromIdentifier('my_product'),
            userIntents: [new SetCategories(['print'])]
        );
        $this->commandMessageBus->dispatch($command);
    }

    /** @test */
    public function it_merges_non_viewable_category_on_update(): void
    {
        $this->createProduct('my_product', [new SetCategories(['print', 'suppliers', 'not_viewable_category'])]);

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('betty'),
            productIdentifier: ProductIdentifier::fromIdentifier('my_product'),
            userIntents: [new SetCategories(['print', 'sales'])]
        );
        $this->commandMessageBus->dispatch($command);

        $this->clearDoctrineUoW();

        // we login as a user that access all categories to check they are still linked to product
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');
        $product = $this->productRepository->findOneByIdentifier('my_product');

        Assert::assertNotNull($product);
        Assert::assertSame('my_product', $product->getIdentifier());
        Assert::assertEqualsCanonicalizing(['print', 'sales', 'suppliers', 'not_viewable_category'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_merges_non_viewable_associated_products_on_replace_products_association(): void
    {
        $this->createProductModel('product_model_non_viewable_by_manager', 'color_variant_accessories', [
            'categories' => ['suppliers'],
        ]);
        $this->createProduct('product_non_viewable_by_manager', [new SetCategories(['suppliers'])]);
        $this->createProduct('product_viewable_by_manager', [
            new ChangeParent('product_model_non_viewable_by_manager'),
            new SetCategories(['print', 'sales']),
            new SetSimpleSelectValue('main_color', null, null, 'green'),
        ]);
        $this->createProduct(
            'my_product',
            [new AssociateProducts('X_SELL', ['product_viewable_by_manager', 'product_non_viewable_by_manager'])]
        );

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('betty'),
            ProductIdentifier::fromIdentifier('my_product'),
            [
                new ReplaceAssociatedProducts('X_SELL', ['product_viewable_by_manager'])
            ]
        );
        $this->commandMessageBus->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->clearDoctrineUoW();

        // we relog as peter to have full permission on categories
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');
        $product = $this->productRepository->findOneByIdentifier('my_product');

        Assert::assertNotNull($product);
        Assert::assertSame('my_product', $product->getIdentifier());
        Assert::assertEqualsCanonicalizing(
            ['product_non_viewable_by_manager', 'product_viewable_by_manager'],
            $this->getAssociatedProductIdentifiers($product)
        );
    }

    /** @test */
    public function it_merges_non_viewable_associated_product_models_on_replace_product_models_association(): void
    {
        $this->createProductModel('product_model_non_viewable_by_manager', 'color_variant_accessories', [
            'categories' => ['suppliers'],
        ]);
        $this->createProductModel('product_model_viewable_by_manager', 'color_variant_accessories', [
            'categories' => ['print', 'sales'],
        ]);
        $this->createProduct('my_product', [
            new SetCategories(['print', 'sales']),
            new AssociateProductModels('X_SELL', ['product_model_non_viewable_by_manager'])
        ]);

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('betty'),
            ProductIdentifier::fromIdentifier('my_product'),
            [
                new ReplaceAssociatedProductModels('X_SELL', ['product_model_viewable_by_manager'])
            ]
        );
        $this->commandMessageBus->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->clearDoctrineUoW();

        // we relog as peter to have full permission on categories
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');
        $product = $this->productRepository->findOneByIdentifier('my_product');

        Assert::assertSame('my_product', $product->getIdentifier());
        Assert::assertEqualsCanonicalizing(
            ['product_model_non_viewable_by_manager', 'product_model_viewable_by_manager'],
            $this->getAssociatedProductModelIdentifiers($product)
        );
    }

    /** @test */
    public function it_merges_non_viewable_associated_products_on_replace_quantified_products_association(): void
    {
        $this->createProductModel('product_model_non_viewable_by_manager', 'color_variant_accessories', [
            'categories' => ['suppliers'],
        ]);
        $this->createProduct('product_non_viewable_by_manager', [new SetCategories(['suppliers'])]);
        $this->createProduct('product_viewable_by_manager', [
            new ChangeParent('product_model_non_viewable_by_manager'),
            new SetCategories(['print', 'sales']),
            new SetSimpleSelectValue('main_color', null, null, 'green'),
        ]);
        $this->createProduct(
            'my_product',
            [new AssociateQuantifiedProducts('bundle', [
                new QuantifiedEntity('product_viewable_by_manager', 10),
                new QuantifiedEntity('product_non_viewable_by_manager', 8),
            ])]
        );

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier(
            $this->getUserId('betty'),
            ProductIdentifier::fromIdentifier('my_product'),
            [new ReplaceAssociatedQuantifiedProducts('bundle', [
                new QuantifiedEntity('product_viewable_by_manager', 7),
            ])]
        ));

        // we relog as peter to have full permission on viewable products
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');
        Assert::assertEqualsCanonicalizing(
            [
                new QuantifiedEntity('product_viewable_by_manager', 7),
                new QuantifiedEntity('product_non_viewable_by_manager', 8),
            ],
            $this->getAssociatedQuantifiedProducts('my_product')
        );
    }

    /** @test */
    public function it_merges_non_viewable_associated_product_models_on_replace_quantified_product_models_association(): void
    {
        $this->createProductModel('product_model_non_viewable_by_manager', 'color_variant_accessories', [
            'categories' => ['suppliers'],
        ]);
        $this->createProductModel('product_model_viewable_by_manager', 'color_variant_accessories', [
            'categories' => ['print', 'sales'],
        ]);
        $this->createProduct('my_product', [
            new SetCategories(['print', 'sales']),
            new AssociateQuantifiedProductModels('bundle', [
                new QuantifiedEntity('product_model_non_viewable_by_manager', 10),
            ]),
        ]);

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('betty');
        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('betty'),
            ProductIdentifier::fromIdentifier('my_product'),
            [
                new ReplaceAssociatedQuantifiedProductModels('bundle', [
                    new QuantifiedEntity('product_model_viewable_by_manager', 10),
                ]),
            ]
        );
        $this->commandMessageBus->dispatch($command);

        // we relog as peter to have full permission on viewable products
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');
        Assert::assertEqualsCanonicalizing(
            [
                new QuantifiedEntity('product_model_non_viewable_by_manager', 10),
                new QuantifiedEntity('product_model_viewable_by_manager', 10),
            ],
            $this->getAssociatedQuantifiedProductModels('my_product')
        );
    }
}
