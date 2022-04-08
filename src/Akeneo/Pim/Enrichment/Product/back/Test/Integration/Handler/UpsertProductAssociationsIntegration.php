<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertProductAssociationsIntegration extends EnrichmentProductTestCase
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

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
    }

    /** @test */
    public function it_updates_a_product_with_associate_product(): void
    {
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProducts('X_SELL', ['associated_product_identifier'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['associated_product_identifier'], $this->getAssociatedProductIdentifiers($updatedProduct));
    }

    /** @test */
    public function it_throws_an_exception_when_associating_with_unknown_identifier(): void
    {
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProducts('X_SELL', ['unknown'])
        ]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Property "associations" expects a valid product identifier. The product does not exist, "unknown" given.');
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_associating_with_unknown_association_type(): void
    {
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProducts('UNKNOWN', ['associated_product_identifier'])
        ]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Property "associations" expects a valid association type code. The association type does not exist or is quantified, "UNKNOWN" given.');
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_update_a_product_with_disassociation(): void
    {
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProducts('X_SELL', ['associated_product_identifier'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['associated_product_identifier'], $this->getAssociatedProductIdentifiers($updatedProduct));
        $this->clearDoctrineUoW();

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new DissociateProducts('X_SELL', ['associated_product_identifier'])
        ]);
        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertEmpty($this->getAssociatedProductIdentifiers($updatedProduct));
    }

    /** @test */
    public function it_replaces_product_associations(): void
    {
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProducts('X_SELL', ['associated_product_identifier'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['associated_product_identifier'], $this->getAssociatedProductIdentifiers($updatedProduct));
        $this->clearDoctrineUoW();

        $command = new UpsertProductCommand(userId: $this->getUserId('peter'), productIdentifier: 'other_associated_product_identifier');
        $this->messageBus->dispatch($command);
        $associatedProduct = $this->productRepository->findOneByIdentifier('other_associated_product_identifier');
        Assert::assertNotNull($associatedProduct);

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new ReplaceAssociatedProducts('X_SELL', ['other_associated_product_identifier'])
        ]);
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['other_associated_product_identifier'], $this->getAssociatedProductIdentifiers($updatedProduct));
    }
}
