<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpsertProductVariantIntegration extends EnrichmentProductTestCase
{
    private MessageBusInterface $messageBus;
    private ProductRepositoryInterface $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
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
        $this->createProduct('variant_product', [
            'parent' => null,
            'categories' => ['suppliers', 'print'],
            'values' => ['main_color' => [['locale' => null, 'scope' => null, 'data' => 'green']]],
        ]);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $command = new UpsertProductCommand(
            userId: $this->getUserId('betty'),
            productIdentifier: 'variant_product',
            parentUserIntent: new SetParent('root')
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertSame('variant_product', $product->getIdentifier());
        Assert::assertEqualsCanonicalizing('root', $product->getParent()->getCode());

        $command = new UpsertProductCommand(
            userId: $this->getUserId('betty'),
            productIdentifier: 'variant_product',
            parentUserIntent: new SetParent('root2')
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertSame('variant_product', $product->getIdentifier());
        Assert::assertEqualsCanonicalizing('root2', $product->getParent()->getCode());

        $command = new UpsertProductCommand(
            userId: $this->getUserId('betty'),
            productIdentifier: 'variant_product',
            parentUserIntent: new RemoveParent()
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('variant_product');
        Assert::assertNotNull($product);
        Assert::assertSame('variant_product', $product->getIdentifier());
        Assert::assertEqualsCanonicalizing(null, $product->getParent());
    }

    /** @test */
    public function it_throws_an_exception_with_unknown_parent_code(): void
    {
        $this->createProduct('variant_product', [
            'parent' => null,
            'categories' => ['suppliers', 'print'],
            'values' => ['main_color' => [['locale' => null, 'scope' => null, 'data' => 'green']]],
        ]);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $command = new UpsertProductCommand(
            userId: $this->getUserId('betty'),
            productIdentifier: 'variant_product',
            parentUserIntent: new SetParent('unknown')
        );

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Property "parent" expects a valid parent code. The parent product model does not exist, "unknown" given.');

        $this->messageBus->dispatch($command);
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }

    private function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
