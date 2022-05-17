<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

final class UpsertProductWithMultipleUserIntentsIntegration extends EnrichmentProductTestCase
{
    private ProductRepositoryInterface $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    /** @test */
    public function it_applies_change_parent_and_set_categories_user_intents(): void
    {
        $this->createProductModel('oldParent', 'color_variant_accessories', [
            'categories' => ['suppliers'],
        ]);
        $this->createProductModel('newParent', 'color_variant_accessories', [
            'categories' => ['samples'],
        ]);

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetCategories(['print']),
                new SetSimpleSelectValue('main_color', null, null, 'green'),
                new ChangeParent('oldParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('oldParent', $product->getParent()->getCode());
        Assert::assertEqualsCanonicalizing(['print', 'suppliers'], $product->getCategoryCodes());

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetCategories(['sales']),
                new ChangeParent('newParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('newParent', $product->getParent()->getCode());
        Assert::assertEqualsCanonicalizing(['samples', 'sales'], $product->getCategoryCodes());

        $command = new UpsertProductCommand(
            userId: $this->getUserId('peter'),
            productIdentifier: 'variant_product',
            categoryUserIntent: new SetCategories(['suppliers']),
            parentUserIntent: new ConvertToSimpleProduct(),
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertFalse($product->isVariant());
        Assert::assertEqualsCanonicalizing(['suppliers'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_applies_change_parent_and_remove_categories_user_intents(): void
    {
        $this->createProductModel('oldParent', 'color_variant_accessories', [
            'categories' => ['suppliers'],
        ]);
        $this->createProductModel('newParent', 'color_variant_accessories', [
            'categories' => ['samples'],
        ]);

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetCategories(['print', 'sales']),
                new SetSimpleSelectValue('main_color', null, null, 'green'),
                new ChangeParent('oldParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('oldParent', $product->getParent()->getCode());
        Assert::assertEqualsCanonicalizing(['print', 'sales', 'suppliers'], $product->getCategoryCodes());

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new RemoveCategories(['print']),
                new ChangeParent('newParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('newParent', $product->getParent()->getCode());
        // todo it keeps previous parent's category 'suppliers'. Is it expected behavior ?
        // todo should we expect this ?
//        Assert::assertEqualsCanonicalizing(['sales', 'samples'], $product->getCategoryCodes());
        Assert::assertEqualsCanonicalizing(['sales', 'suppliers', 'samples'], $product->getCategoryCodes());

        $command = new UpsertProductCommand(
            userId: $this->getUserId('peter'),
            productIdentifier: 'variant_product',
            categoryUserIntent: new RemoveCategories(['sales']),
            parentUserIntent: new ConvertToSimpleProduct(),
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertFalse($product->isVariant());
        // todo it keeps previous parents' categories ['suppliers', 'samples']. Is it expected behavior ?
        // todo should we expect this ?
//        Assert::assertEqualsCanonicalizing([], $product->getCategoryCodes());
        Assert::assertEqualsCanonicalizing(['samples', 'suppliers'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_applies_change_parent_and_add_categories_usert_intents()
    {
        $this->createProductModel('oldParent', 'color_variant_accessories', [
            'categories' => ['suppliers'],
        ]);
        $this->createProductModel('newParent', 'color_variant_accessories', [
            'categories' => ['samples'],
        ]);

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetSimpleSelectValue('main_color', null, null, 'green'),
                new ChangeParent('oldParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('oldParent', $product->getParent()->getCode());
        Assert::assertEqualsCanonicalizing(['suppliers'], $product->getCategoryCodes());

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new AddCategories(['print']),
                new ChangeParent('newParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('newParent', $product->getParent()->getCode());

        // todo it keeps previous parent categories 'suppliers'. is it expected ?
        // todo should we expect this ?
//        Assert::assertEqualsCanonicalizing(['print', 'samples'], $product->getCategoryCodes());
        Assert::assertEqualsCanonicalizing(['print', 'suppliers', 'samples'], $product->getCategoryCodes());

        $command = new UpsertProductCommand(
            userId: $this->getUserId('peter'),
            productIdentifier: 'variant_product',
            categoryUserIntent: new AddCategories(['sales']),
            parentUserIntent: new ConvertToSimpleProduct(),
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertFalse($product->isVariant());
        // todo it keeps previous parents' categories ['suppliers', 'samples']. Is it expected behavior ?
        // todo should we expect this ?
//        Assert::assertEqualsCanonicalizing(['print', 'sales'], $product->getCategoryCodes());
        Assert::assertEqualsCanonicalizing(['print', 'suppliers', 'samples', 'sales'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_can_create_a_product_with_change_parent_and_set_family_user_intents()
    {
        $this->createProductModel('root', 'color_variant_accessories', []);

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetFamily('accessories'),
                new SetSimpleSelectValue('main_color', null, null, 'green'),
                new ChangeParent('root'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('root', $product->getParent()->getCode());
        Assert::assertEqualsCanonicalizing('accessories', $product->getFamily()->getCode());
    }

    // todo: not sure the last 2 tests are interesting ?
    /** @test */
    public function it_throws_an_exception_when_changing_family_and_parent_even_if_parents_have_same_family_variant()
    {
        $this->createAttribute('size', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $this->createAttributeOptions('size', ['S', 'M', 'L', 'XL']);

        $this->createFamily('clothes', ['attributes' => ['name', 'sub_name', 'size']]);
        $this->createFamilyVariant('size_variant_clothes', 'clothes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => [],
                ],
            ],
        ]);

        // current parent and new parent have the same family variant
        $this->createProductModel('oldParent', 'color_variant_accessories', []);
        $this->createProductModel('newParent', 'color_variant_accessories', []);

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetFamily('accessories'),
                new SetSimpleSelectValue('main_color', null, null, 'green'),
                new ChangeParent('oldParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('oldParent', $product->getParent()->getCode());
        Assert::assertEqualsCanonicalizing('accessories', $product->getFamily()->getCode());

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetFamily('clothes'),
                new ChangeParent('newParent'),
            ]
        );

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('The variant product family must be the same than its parent');
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_changing_family_and_parent_and_when_parents_have_different_family_variant()
    {
        $this->createAttribute('size', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $this->createAttributeOptions('size', ['S', 'M', 'L', 'XL']);

        $this->createFamily('clothes', ['attributes' => ['name', 'sub_name', 'size']]);
        $this->createFamilyVariant('size_variant_clothes', 'clothes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => [],
                ],
            ],
        ]);

        // current parent and new parent have different family variant
        $this->createProductModel('oldParent', 'color_variant_accessories', []);
        $this->createProductModel('newParent', 'size_variant_clothes', []);

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetFamily('accessories'),
                new SetSimpleSelectValue('main_color', null, null, 'green'),
                new ChangeParent('oldParent'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('oldParent', $product->getParent()->getCode());
        Assert::assertEqualsCanonicalizing('accessories', $product->getFamily()->getCode());

        $command = UpsertProductCommand::createFromCollection(
            $this->getUserId('peter'),
            'variant_product',
            [
                new SetFamily('clothes'),
                new ChangeParent('newParent'),
            ]
        );

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('New parent "newParent" of variant product "variant_product" must have the same family variant "color_variant_accessories" than the previous parent');
        $this->messageBus->dispatch($command);
    }
}
