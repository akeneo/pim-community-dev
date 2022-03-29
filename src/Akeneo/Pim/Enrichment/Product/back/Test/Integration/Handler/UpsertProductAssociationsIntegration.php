<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddAssociatedProducts;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertProductAssociationsIntegration extends EnrichmentProductTestCase
{
    private MessageBusInterface $messageBus;
    private ProductRepositoryInterface $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    /** @test */
    public function it_update_a_product_with_add_association(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEmpty($product->getAssociations());

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'associated_product_identifier');
        $this->messageBus->dispatch($command);
        $associatedProduct = $this->productRepository->findOneByIdentifier('associated_product_identifier');
        Assert::assertNotNull($associatedProduct);

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $command = UpsertProductCommand::createFromCollection($this->getUserId('admin'), 'identifier', [
            new AddAssociatedProducts('X_SELL', ['associated_product_identifier'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        $associatedIdentifiers = $updatedProduct->getAssociatedProducts('X_SELL')
                ?->map(fn (ProductInterface $product): string => $product->getIdentifier())
                ?->toArray() ?? [];
        Assert::assertSame(['associated_product_identifier'], $associatedIdentifiers);
    }

    /** @test */
    public function it_throws_an_exception_when_adding_association_with_unknown_identifier(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEmpty($product->getAssociations());

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $command = UpsertProductCommand::createFromCollection($this->getUserId('admin'), 'identifier', [
            new AddAssociatedProducts('X_SELL', ['associated_product_identifier'])
        ]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Property "associations" expects a valid product identifier. The product does not exist, "associated_product_identifier" given.');
        $this->messageBus->dispatch($command);
    }
}
