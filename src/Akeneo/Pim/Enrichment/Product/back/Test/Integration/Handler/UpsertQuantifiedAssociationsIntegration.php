<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedProduct;
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

        $this->messageBus->dispatch(new UpsertProductCommand(userId: $this->getUserId('peter'), productIdentifier: 'identifier'));
        Assert::assertNotNull($this->productRepository->findOneByIdentifier('identifier'));

        $this->messageBus->dispatch(new UpsertProductCommand(userId: $this->getUserId('peter'), productIdentifier: 'associated_product1'));
        Assert::assertNotNull($this->productRepository->findOneByIdentifier('associated_product1'));

        $this->messageBus->dispatch(new UpsertProductCommand(userId: $this->getUserId('peter'), productIdentifier: 'associated_product2'));
        Assert::assertNotNull($this->productRepository->findOneByIdentifier('associated_product1'));

        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
    }

    /** @test */
    public function it_associates_a_quantified_product(): void
    {
        Assert::assertEmpty($this->getAssociatedQuantifiedProducts('identifier'));
        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('associated_product1', 5)]),
        ]));
        Assert::assertEquals(
            [new QuantifiedProduct('associated_product1', 5)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('associated_product1', 8)]),
        ]));
        Assert::assertEquals(
            [new QuantifiedProduct('associated_product1', 8)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );
    }

    /** @test */
    public function it_cannot_associate_an_unknown_product(): void
    {
        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage("The following products don't exist: unknown. Please make sure the products haven't been deleted in the meantime.");

        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('unknown', 5)]),
        ]));
    }

    /** @test */
    public function it_dissociates_a_quantified_product(): void
    {
        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateQuantifiedProducts('bundle', [
                new QuantifiedProduct('associated_product1', 5),
                new QuantifiedProduct('associated_product2', 4),
            ]),
        ]));
        Assert::assertEquals(
            [new QuantifiedProduct('associated_product1', 5), new QuantifiedProduct('associated_product2', 4)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new DissociateQuantifiedProducts('bundle', ['associated_product1']),
        ]));
        Assert::assertEquals(
            [new QuantifiedProduct('associated_product2', 4)],
            $this->getAssociatedQuantifiedProducts('identifier')
        );

        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new DissociateQuantifiedProducts('bundle', ['associated_product2', 'unknown']),
        ]));
        Assert::assertEmpty($this->getAssociatedQuantifiedProducts('identifier'));
    }
}
