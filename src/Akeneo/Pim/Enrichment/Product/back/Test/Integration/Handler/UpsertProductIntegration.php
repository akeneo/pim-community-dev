<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMetricValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpsertProductIntegration extends TestCase
{
    private MessageBusInterface $messageBus;
    private ProductRepositoryInterface $productRepository;

    private const TEXT_AREA_VALUE = "<p><span style=\"font-weight: bold;\">title</span></p><p>text</p>";

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

        $this->assertProductHasCorrectValueByAttributeCode('a_text', 'foo');
    }

    /** @test */
    public function it_updates_a_product_with_a_text_value(): void
    {
        $this->updateProduct(new SetTextValue('a_text', null, null, 'foo'));

        $this->assertProductHasCorrectValueByAttributeCode('a_text', 'foo');
    }

    /** @test */
    public function it_creates_a_product_with_a_number_value(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetNumberValue('a_number_integer', null, null, 10),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $this->assertProductHasCorrectValueByAttributeCode('a_number_integer', 10);
    }

    /** @test */
    public function it_updates_a_product_with_a_number_value(): void
    {
        $this->updateProduct(new SetNumberValue('a_number_integer', null, null, 10));
        $this->assertProductHasCorrectValueByAttributeCode('a_number_integer', 10);
    }

    /** @test */
    public function it_creates_a_product_with_a_textarea_value(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextareaValue('a_text_area', null, null, self::TEXT_AREA_VALUE),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $this->assertProductHasCorrectValueByAttributeCode('a_text_area', self::TEXT_AREA_VALUE);
    }

    /** @test */
    public function it_updates_a_product_with_a_textarea_value(): void
    {
        $this->updateProduct(new SetTextareaValue('a_text_area', null, null, self::TEXT_AREA_VALUE));
        $this->assertProductHasCorrectValueByAttributeCode('a_text_area', self::TEXT_AREA_VALUE);
    }

    /** @test */
    public function it_updates_a_product_with_a_metric_value(): void
    {
        // Creates empty product
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        // Update product with number value
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetMetricValue('a_metric', null, null, '100', 'KILOWATT'),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue('a_metric', null, null);
        Assert::assertNotNull($value);

        Assert::assertEquals(100, $value->getData()->getData());
        Assert::assertEquals('KILOWATT', $value->getData()->getUnit());
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

    /** @test */
    public function it_clears_values(): void
    {
        // create product with values from every type of attribute
        $this->createProduct('complex_product', 'familyA', [
            'a_date' => [['locale' => null, 'scope' => null, 'data' => '2010-10-10']],
            'a_file' => [['locale' => null, 'scope' => null, 'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.txt'))]],
            'a_metric' => [['locale' => null, 'scope' => null, 'data' => ['amount' => 1, 'unit' => 'WATT']]],
            'a_multi_select' => [['locale' => null, 'scope' => null, 'data' => ['optionA']]],
            'a_number_float' => [['locale' => null, 'scope' => null, 'data' => 3.14]],
            'a_number_float_negative' => [['locale' => null, 'scope' => null, 'data' => -3.14]],
            'a_number_integer' => [['locale' => null, 'scope' => null, 'data' => 42]],
            'a_ref_data_multi_select' => [['locale' => null, 'scope' => null, 'data' => ['brilliantine', 'tapestry', 'zibeline']]],
            'a_ref_data_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'bright-pink']],
            'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionA']],
            'a_text' => [['locale' => null, 'scope' => null, 'data' => 'foo']],
            'a_text_area' => [['locale' => null, 'scope' => null, 'data' => self::TEXT_AREA_VALUE]],
            'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
            'an_image' => [['locale' => null, 'scope' => null, 'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))]],
        ]);

        $product = $this->productRepository->findOneByIdentifier('complex_product');
        Assert::assertNotNull($product);
        Assert::assertNotNull($product->getValue('a_text', null, null));

        // Update product with clear values
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            new ClearValue('a_date', null, null),
            new ClearValue('a_file', null, null),
            new ClearValue('a_metric', null, null),
            new ClearValue('a_multi_select', null, null),
            new ClearValue('a_number_float', null, null),
            new ClearValue('a_number_float_negative', null, null),
            new ClearValue('a_number_integer', null, null),
            new ClearValue('a_ref_data_multi_select', null, null),
            new ClearValue('a_ref_data_simple_select', null, null),
            new ClearValue('a_simple_select', null, null),
            new ClearValue('a_text', null, null),
            new ClearValue('a_text_area', null, null),
            new ClearValue('a_yes_no', null, null),
            new ClearValue('an_image', null, null),
            // clear a value not related to a product
            new ClearValue('a_number_float_very_decimal', null, null)
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);

        Assert::assertNull($product->getValue('a_date', null, null));
        Assert::assertNull($product->getValue('a_file', null, null));
        Assert::assertNull($product->getValue('a_metric', null, null));
        Assert::assertNull($product->getValue('a_multi_select', null, null));
        Assert::assertNull($product->getValue('a_number_float', null, null));
        Assert::assertNull($product->getValue('a_number_float_negative', null, null));
        Assert::assertNull($product->getValue('a_number_integer', null, null));
        Assert::assertNull($product->getValue('a_ref_data_multi_select', null, null));
        Assert::assertNull($product->getValue('a_ref_data_simple_select', null, null));
        Assert::assertNull($product->getValue('a_simple_select', null, null));
        Assert::assertNull($product->getValue('a_text', null, null));
        Assert::assertNull($product->getValue('a_text_area', null, null));
        Assert::assertNull($product->getValue('a_yes_no', null, null));
        Assert::assertNull($product->getValue('an_image', null, null));
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }

    private function assertProductHasCorrectValueByAttributeCode(string $attributeCode, mixed $expectedValue): void
    {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue($attributeCode, null, null);
        Assert::assertNotNull($value);
        Assert::assertSame($expectedValue, $value->getData());
    }

    private function updateProduct(ValueUserIntent $userIntent): void
    {
        // Creates empty product
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        // Update product with userIntent value
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valuesUserIntent: [
            $userIntent
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
    }

    private function createProduct(string $identifier, ?string $familyCode, array $values): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf('Impossible to setup test in %s: %s', static::class, $errors->get(0)->getMessage()));
        }
        $this->get('pim_catalog.saver.product')->save($product);
    }

    protected function getFileInfoKey(string $path): string
    {
        if (!is_file($path)) {
            throw new \Exception(sprintf('The path "%s" does not exist.', $path));
        }

        $fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $fileInfo = $fileStorer->store(new \SplFileInfo($path), FileStorage::CATALOG_STORAGE_ALIAS);

        return $fileInfo->getKey();
    }

    /**
     * Look in every fixture directory if a fixture $name exists.
     * And return the pathname of the fixture if it exists.
     *
     * @param string $name
     *
     * @throws \Exception if no fixture $name has been found
     *
     * @return string
     */
    protected function getFixturePath(string $name): string
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFixtureDirectories() as $fixtureDirectory) {
            $path = $fixtureDirectory . DIRECTORY_SEPARATOR . $name;
            if (is_file($path) && false !== realpath($path)) {
                return realpath($path);
            }
        }

        throw new \Exception(sprintf('The fixture "%s" does not exist.', $name));
    }
}
