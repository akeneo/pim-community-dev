<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
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

        $command = new UpsertProductCommand(userId: $this->getUserId('peter'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEmpty($product->getAssociations());

        $command = new UpsertProductCommand(userId: $this->getUserId('peter'), productIdentifier: 'associated_product_identifier');
        $this->messageBus->dispatch($command);
        $associatedProduct = $this->productRepository->findOneByIdentifier('associated_product_identifier');
        Assert::assertNotNull($associatedProduct);

        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
    }

    /** @test */
    public function it_associates_a_quantified_product(): void
    {
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertEquals(
            [],
            $this->getAssociatedQuantifiedProducts($updatedProduct)
        );
        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('associated_product_identifier', 5)]),
        ]));
        $this->clearDoctrineUoW();
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertEquals(
            [new QuantifiedProduct('associated_product_identifier', 5)],
            $this->getAssociatedQuantifiedProducts($updatedProduct)
        );

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->messageBus->dispatch(UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('associated_product_identifier', 8)]),
        ]));
        $this->clearDoctrineUoW();
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertEquals(
            [new QuantifiedProduct('associated_product_identifier', 8)],
            $this->getAssociatedQuantifiedProducts($updatedProduct)
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
}
