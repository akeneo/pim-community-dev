<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

final class UpsertProductVariantIntegration extends EnrichmentProductTestCase
{
    private ProductRepositoryInterface $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->commandMessageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');
    }

    /** @test */
    public function it_can_add_change_and_remove_a_parent(): void
    {
        $this->createProductModel('root', 'color_variant_accessories', [
            'categories' => ['print'],
        ]);
        $this->createProductModel('root2', 'color_variant_accessories', [
            'categories' => ['print'],
        ]);

        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('peter'),
            ProductIdentifier::fromIdentifier('variant_product'),
            [
                new SetCategories(['suppliers', 'print']),
                new SetSimpleSelectValue('main_color', null, null, 'green'),
            ]
        );
        $this->commandMessageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier('variant_product'),
            userIntents: [new ChangeParent('root')]
        );
        $this->commandMessageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('root', $product->getParent()->getCode());

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier('variant_product'),
            userIntents: [new ChangeParent('root2')]
        );
        $this->commandMessageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('root2', $product->getParent()->getCode());

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier('variant_product'),
            userIntents: [new ConvertToSimpleProduct()]
        );
        $this->commandMessageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertFalse($product->isVariant());
    }

    /** @test */
    public function it_can_only_change_parent_to_another_family_by_clearing_parent_first(): void
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

        $this->createProductModel('root', 'color_variant_accessories', [
            'categories' => ['print'],
        ]);
        $this->createProductModel('root2', 'size_variant_clothes', [
            'categories' => ['print'],
        ]);

        $command = UpsertProductCommand::createWithIdentifier(
            $this->getUserId('peter'),
            ProductIdentifier::fromIdentifier('variant_product'),
            [
                new ChangeParent('root'),
                new SetCategories(['suppliers', 'print']),
                new SetSimpleSelectValue('main_color', null, null, 'green'),
            ]
        );
        $this->commandMessageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier('variant_product'),
            userIntents: [new ChangeParent('root2')]
        );

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('New parent "root2" of variant product "variant_product" must have the same family variant "color_variant_accessories" than the previous parent');
        $this->commandMessageBus->dispatch($command);

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier('variant_product'),
            userIntents: [new ConvertToSimpleProduct()]
        );
        $this->commandMessageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing(null, $product->getParent());

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier('variant_product'),
            userIntents: [new ChangeParent('root2')]
        );
        $this->commandMessageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing('root2', $product->getParent()->getCode());
    }

    /** @test */
    public function it_throws_an_exception_with_unknown_parent_code(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('peter'),
            productIdentifier: ProductIdentifier::fromIdentifier('variant_product'),
            userIntents: [new ChangeParent('unknown')]
        );

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Property "parent" expects a valid parent code. The parent product model does not exist, "unknown" given.');

        $this->commandMessageBus->dispatch($command);
    }
}
