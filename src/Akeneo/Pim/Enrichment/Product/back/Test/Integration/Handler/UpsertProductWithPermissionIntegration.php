<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpsertProductWithPermissionIntegration extends EnrichmentProductTestCase
{
    private MessageBusInterface $messageBus;
    private ProductRepositoryInterface $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        FeatureHelper::skipIntegrationTestWhenPermissionFeatureIsNotActivated();
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    /** @test */
    public function it_throws_an_exception_when_user_category_is_not_granted(): void
    {
        // Creates empty product (use command/handler when we can set a category)
        $this->createProduct('identifier', ['categories' => ['print']]);

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('You don\'t have access to products in any tree, please contact your administrator');

        $command = new UpsertProductCommand(userId: $this->getUserId('mary'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_user_locale_is_not_granted(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('You don\'t have access to product data in any activated locale, please contact your administrator');

        $command = new UpsertProductCommand(userId: $this->getUserId('mary'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextValue('name', null, 'en_GB', 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_creates_a_new_uncategorized_product(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('mary'), productIdentifier: 'new_product', valuesUserIntent: [
            new SetTextValue('name', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('new_product');
        Assert::assertNotNull($product);
        Assert::assertSame('new_product', $product->getIdentifier());
        Assert::assertNotNull($product->getValue('name'));
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
