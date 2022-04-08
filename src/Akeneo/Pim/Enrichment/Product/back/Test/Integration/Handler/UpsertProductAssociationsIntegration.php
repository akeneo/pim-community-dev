<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
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

    /** @test */
    public function it_update_a_product_with_associate_product_model(): void
    {
        $this->createProductModel('product_model_identifier', 'color_variant_accessories', []);

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProductModels('X_SELL', ['product_model_identifier'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['product_model_identifier'], $this->getAssociatedProductModelIdentifiers($updatedProduct));
    }

    /** @test */
    public function it_throws_an_exception_when_associating_with_unknown_product_model_identifier(): void
    {
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProductModels('X_SELL', ['unknown'])
        ]);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Property "associations" expects a valid product model identifier. The product model does not exist, "unknown" given.');
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_update_a_product_with_dissociate_product_model(): void
    {
        $this->createProductModel('product_model_identifier', 'color_variant_accessories', []);

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProductModels('X_SELL', ['product_model_identifier'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['product_model_identifier'], $this->getAssociatedProductModelIdentifiers($updatedProduct));

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new DissociateProductModels('X_SELL', ['product_model_identifier'])
        ]);
        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertEmpty($this->getAssociatedProductModelIdentifiers($updatedProduct));
    }

    /** @test */
    public function it_replaces_product_model_associations(): void
    {
        $this->createProductModel('product_model_identifier', 'color_variant_accessories', []);
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateProductModels('X_SELL', ['product_model_identifier'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['product_model_identifier'], $this->getAssociatedProductModelIdentifiers($updatedProduct));
        $this->clearDoctrineUoW();

        $this->createProductModel('other_product_model', 'color_variant_accessories', []);
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new ReplaceAssociatedProductModels('X_SELL', ['other_product_model'])
        ]);
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['other_product_model'], $this->getAssociatedProductModelIdentifiers($updatedProduct));
    }

    /**
     * @return array<string>
     */
    public function getAssociatedProductModelIdentifiers(ProductInterface $product): array
    {
        return $product->getAssociatedProductModels('X_SELL')
            ?->map(fn (ProductModelInterface $productModel): string => $productModel->getIdentifier())
            ?->toArray() ?? [];
    }

    /** @test */
    public function it_updates_a_product_with_associate_groups(): void
    {
        $this->createGroup('associated_group_code');

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateGroups('X_SELL', ['associated_group_code'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['associated_group_code'], $this->getAssociatedGroupIdentifiers($updatedProduct));
    }

    /** @test */
    public function it_update_a_product_with_group_disassociation(): void
    {
        $this->createGroup('associated_group_code');
        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateGroups('X_SELL', ['associated_group_code'])
        ]);

        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['associated_group_code'], $this->getAssociatedGroupIdentifiers($updatedProduct));
        $this->clearDoctrineUoW();

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new DissociateGroups('X_SELL', ['associated_group_code'])
        ]);
        $this->messageBus->dispatch($command);
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertEmpty($this->getAssociatedGroupIdentifiers($updatedProduct));
    }

    /** @test */
    public function it_replaces_group_associations(): void
    {
        $this->createGroup('associated_group_code');
        $this->createGroup('other_associated_group_code');

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new AssociateGroups('X_SELL', ['associated_group_code'])
        ]);
        $this->messageBus->dispatch($command);

        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['associated_group_code'], $this->getAssociatedGroupIdentifiers($updatedProduct));
        $this->clearDoctrineUoW();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $command = UpsertProductCommand::createFromCollection($this->getUserId('peter'), 'identifier', [
            new ReplaceAssociatedGroups('X_SELL', ['other_associated_group_code'])
        ]);
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();
        $updatedProduct = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertSame(['other_associated_group_code'], $this->getAssociatedGroupIdentifiers($updatedProduct));
    }

    private function createGroup(string $groupCode): GroupInterface
    {
        $group = $this->get('pim_catalog.factory.group')->createGroup('RELATED');
        $this->get('pim_catalog.updater.group')->update($group, [
            'code' => $groupCode
        ]);

        $errors = $this->get('validator')->validate($group);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf('Impossible to setup test in %s: %s', static::class, $errors->get(0)->getMessage()));
        }

        $this->get('pim_catalog.saver.group')->save($group);

        return $group;
    }

    /**
     * @return array<string>
     */
    private function getAssociatedGroupIdentifiers(ProductInterface $product): array
    {
        return $product->getAssociatedGroups('X_SELL')
                ?->map(fn (GroupInterface $group): string => (string) $group->getCode())
                ?->toArray() ?? [];
    }
}
