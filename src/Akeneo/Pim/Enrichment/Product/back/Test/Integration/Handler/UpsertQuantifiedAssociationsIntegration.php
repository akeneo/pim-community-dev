<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertQuantifiedAssociationsIntegration extends EnrichmentProductTestCase
{
    private ProductRepositoryInterface $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('peter');

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('peter'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: []));
        Assert::assertNotNull($this->productRepository->findOneByIdentifier('identifier'));

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('peter'), productIdentifier: ProductIdentifier::fromIdentifier('associated_product1'), userIntents: []));
        Assert::assertNotNull($this->productRepository->findOneByIdentifier('associated_product1'));

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('peter'), productIdentifier: ProductIdentifier::fromIdentifier('associated_product2'), userIntents: []));
        Assert::assertNotNull($this->productRepository->findOneByIdentifier('associated_product1'));

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('peter'), productIdentifier: ProductIdentifier::fromIdentifier('associated_product3'), userIntents: []));
        Assert::assertNotNull($this->productRepository->findOneByIdentifier('associated_product3'));

        $this->createProductModel('product_model1', 'color_variant_accessories', []);
        $this->createProductModel('product_model2', 'color_variant_accessories', []);
        $this->createProductModel('product_model3', 'color_variant_accessories', []);

        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
    }

    /** @test */
    public function it_associates_a_quantified_product(): void
    {
        Assert::assertEmpty($this->getAssociatedQuantifiedProducts('identifier'));
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('associated_product1', 5)]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('associated_product1', 5)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('associated_product1', 8)]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('associated_product1', 8)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );
    }

    /** @test */
    public function it_cannot_associate_an_unknown_product(): void
    {
        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage("The following products don't exist: unknown. Please make sure the products haven't been deleted in the meantime.");

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('unknown', 5)]),
        ]));
    }

    /** @test */
    public function it_dissociates_a_quantified_product(): void
    {
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new AssociateQuantifiedProducts('bundle', [
                new QuantifiedEntity('associated_product1', 5),
                new QuantifiedEntity('associated_product2', 4),
            ]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('associated_product1', 5), new QuantifiedEntity('associated_product2', 4)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new DissociateQuantifiedProducts('bundle', ['associated_product1']),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('associated_product2', 4)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new DissociateQuantifiedProducts('bundle', ['associated_product2', 'unknown']),
        ]));
        Assert::assertEmpty($this->getAssociatedQuantifiedProducts('identifier'));
    }

    /** @test */
    public function it_replaces_associated_quantified_products(): void
    {
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new ReplaceAssociatedQuantifiedProducts('bundle', [
                new QuantifiedEntity('associated_product1', 5),
                new QuantifiedEntity('associated_product2', 4),
            ]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('associated_product1', 5), new QuantifiedEntity('associated_product2', 4)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new ReplaceAssociatedQuantifiedProducts('bundle', [
                new QuantifiedEntity('associated_product1', 50),
                new QuantifiedEntity('associated_product3', 2),
            ]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('associated_product1', 50), new QuantifiedEntity('associated_product3', 2)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );
    }

    /** @test */
    public function it_associates_a_quantified_product_model(): void
    {
        Assert::assertEmpty($this->getAssociatedQuantifiedProductModels('identifier'));
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new AssociateQuantifiedProductModels('bundle', [new QuantifiedEntity('product_model1', 5)]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('product_model1', 5)],
            $this->getAssociatedQuantifiedProductModels('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new AssociateQuantifiedProductModels('bundle', [new QuantifiedEntity('product_model1', 8)]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('product_model1', 8)],
            $this->getAssociatedQuantifiedProductModels('identifier')
        );
    }

    /** @test */
    public function it_dissociates_a_quantified_product_model(): void
    {
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new AssociateQuantifiedProductModels('bundle', [
                new QuantifiedEntity('product_model1', 5),
                new QuantifiedEntity('product_model2', 4),
            ]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('product_model1', 5), new QuantifiedEntity('product_model2', 4)],
            $this->getAssociatedQuantifiedProductModels('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new DissociateQuantifiedProductModels('bundle', ['product_model1']),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('product_model2', 4)],
            $this->getAssociatedQuantifiedProductModels('identifier')
        );

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new DissociateQuantifiedProductModels('bundle', ['product_model2', 'unknown']),
        ]));
        Assert::assertEmpty($this->getAssociatedQuantifiedProductModels('identifier'));
    }

    /** @test */
    public function it_replaces_associated_quantified_product_models(): void
    {
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new ReplaceAssociatedQuantifiedProductModels('bundle', [
                new QuantifiedEntity('product_model1', 5),
                new QuantifiedEntity('product_model2', 4),
            ]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('product_model1', 5), new QuantifiedEntity('product_model2', 4)],
            $this->getAssociatedQuantifiedProductModels('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('peter'), ProductIdentifier::fromIdentifier('identifier'), [
            new ReplaceAssociatedQuantifiedProductModels('bundle', [
                new QuantifiedEntity('product_model1', 50),
                new QuantifiedEntity('product_model3', 2),
            ]),
        ]));
        Assert::assertEquals(
            [new QuantifiedEntity('product_model1', 50), new QuantifiedEntity('product_model3', 2)],
            $this->getAssociatedQuantifiedProductModels('identifier')
        );
    }
}
