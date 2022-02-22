<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpsertProductIntegration extends TestCase
{
    private MessageBusInterface $messageBus;
    private ProductRepositoryInterface $productRepository;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    private function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    /** @test */
    public function it_creates_an_empty_product(): void
    {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertSame('identifier', $product->getIdentifier());
    }

    /** @test */
    public function it_creates_a_product_with_a_text_value(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue('a_text', null, null);
        Assert::assertNotNull($value);
        Assert::assertSame('foo', $value->getData());
    }

    /** @test */
    public function it_updates_a_product_with_a_text_value(): void
    {
        // Creates empty product
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        // Update product with text value
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue('a_text', null, null);
        Assert::assertNotNull($value);
        Assert::assertSame('foo', $value->getData());
    }

    /** @test */
    public function it_creates_a_product_with_a_number_value(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetNumberValue('a_number_integer', null, null, 10),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue('a_number_integer', null, null);
        Assert::assertNotNull($value);
        Assert::assertSame(10, $value->getData());
    }

    /** @test */
    public function it_updates_a_product_with_a_number_value(): void
    {
        // Creates empty product
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        // Update product with number value
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetNumberValue('a_number_integer', null, null, 10),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue('a_number_integer', null, null);
        Assert::assertNotNull($value);
        Assert::assertSame(10, $value->getData());
    }

    /** @test */
    public function it_throws_an_exception_when_giving_a_locale_for_a_non_localizable_product(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The a_text attribute does not require a locale, "en_US" was detected');

        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextValue('a_text', null, 'en_US', 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_an_unknown_user(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "0" user does not exist');

        $command = new UpsertProductCommand(userId: 0, productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_an_empty_product_identifier(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The product identifier requires a non empty string');

        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: '', valuesUserIntent: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }
}
