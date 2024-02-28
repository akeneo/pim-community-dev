<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\RemoveFromGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
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
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
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

        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: []);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertSame('identifier', $product->getIdentifier());
    }

    /** @test */
    public function it_creates_a_product_with_a_text_value(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
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
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetNumberValue('a_number_integer', null, null, '10'),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $this->assertProductHasCorrectValueByAttributeCode('a_number_integer', '10');
    }

    /** @test */
    public function it_updates_a_product_with_a_number_value(): void
    {
        $this->updateProduct(new SetNumberValue('a_number_integer', null, null, '10'));
        $this->assertProductHasCorrectValueByAttributeCode('a_number_integer', '10');
    }

    /** @test */
    public function it_creates_a_product_with_a_textarea_value(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
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
    public function it_updates_a_product_s_identifier(): void
    {
        $this->updateProduct(new SetIdentifierValue('sku', 'new_identifier'));
        Assert::assertNull($this->productRepository->findOneByIdentifier('identifier'));
        $updatedProduct = $this->productRepository->findOneByIdentifier('new_identifier');
        Assert::assertNotNull($updatedProduct);
        Assert::assertSame('new_identifier', $updatedProduct->getIdentifier());
        Assert::assertSame('new_identifier', $updatedProduct->getValue('sku')?->getData());
    }

    /** @test */
    public function it_allows_to_delete_the_identifier()
    {
        $this->updateProduct(new SetTextValue('a_text', null, null, 'foo'));
        $productUuid = $this->productRepository->findOneByIdentifier('identifier')->getUuid();
        $this->updateProduct(new SetIdentifierValue('sku', ''));
        Assert::assertNull($this->productRepository->findOneByIdentifier('identifier'));
        Assert::assertEmpty($this->productRepository->find($productUuid)->getIdentifier());
    }

    /** @test */
    public function it_throws_an_exception_for_a_duplicate_identifier_value(): void
    {
        $this->messageBus->dispatch(UpsertProductCommand::createWithIdentifier(
            $this->getUserId('admin'),
            ProductIdentifier::fromIdentifier('foo'),
            []
        ));

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('The foo identifier is already used for another product.');
        $this->updateProduct(new SetIdentifierValue('sku', 'foo'));
    }

    /** @test */
    public function it_updates_a_product_with_a_measurement_value(): void
    {
        // Creates empty product
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: []);
        $this->messageBus->dispatch($command);
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        // Update product with number value
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetMeasurementValue('a_metric', null, null, '100', 'KILOWATT'),
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
    public function it_throws_an_exception_when_a_measurement_unit_is_unknown(): void
    {
        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('Please specify a valid metric unit');
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetMeasurementValue('a_metric', null, null, '1275', 'unknown'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_a_locale_for_a_non_localizable_product(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The a_text attribute does not require a locale, "en_US" was detected');

        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetTextValue('a_text', null, 'en_US', 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_an_unknown_user(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "0" user does not exist');

        $command = UpsertProductCommand::createWithIdentifier(userId: 0, productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_an_empty_product_identifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The product identifier requires a non empty string');

        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier(''), userIntents: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_clears_values(): void
    {
        // create product with values from every type of attribute
        $this->createProduct('complex_product', 'familyA', [
            new SetDateValue('a_date', null, null, new \DateTime('2010-10-10')),
            new SetFileValue('a_file', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.txt'))),
            new SetMeasurementValue('a_metric', null, null, 1, 'WATT'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA']),
            new SetNumberValue('a_number_float', null, null, '3.14'),
            new SetNumberValue('a_number_float_negative', null, null, '-3.14'),
            new SetNumberValue('a_number_integer', null, null, '42'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, ['brilliantine', 'tapestry', 'zibeline']),
            new SetSimpleReferenceEntityValue('a_ref_data_simple_select', null, null, 'bright-pink'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
            new SetTextValue('a_text', null, null, 'foo'),
            new SetTextareaValue('a_text_area', null, null, self::TEXT_AREA_VALUE),
            new SetBooleanValue('a_yes_no', null, null, true),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
        ]);

        $product = $this->productRepository->findOneByIdentifier('complex_product');
        Assert::assertNotNull($product);
        Assert::assertNotNull($product->getValue('a_text', null, null));

        // Update product with clear values
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
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

    /** @test */
    public function it_clears_asset_collection_value(): void
    {
        $this->loadAssetFixtures();

        $this->createProduct(
            'product_with_asset',
            'familyA',
            [new SetAssetValue('packshot_attr', null, null, ['packshot1'])]
        );
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $product = $this->productRepository->findOneByIdentifier('product_with_asset');
        Assert::assertNotNull($product->getValue('packshot_attr', null, null));

        // Update product with clear values
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('product_with_asset'), userIntents: [
            new ClearValue('packshot_attr', null, null),
        ]);

        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('product_with_asset');
        Assert::assertNull($product->getValue('packshot_attr', null, null));
    }

    /** @test */
    public function it_clears_reference_entity_value(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createRecords('brand', ['Akeneo', 'Other']);

        $this->createAttribute(
            [
                'code' => 'a_reference_entity_attribute',
                'type' => 'akeneo_reference_entity',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createAttribute(
            [
                'code' => 'a_reference_entity_collection_attribute',
                'type' => 'akeneo_reference_entity_collection',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );

        $this->createProduct(
            'product_with_ref_entities',
            'familyA',
            [
                new SetSimpleReferenceEntityValue('a_reference_entity_attribute', null, null, 'Akeneo'),
                new SetMultiReferenceEntityValue('a_reference_entity_collection_attribute', null, null, ['Akeneo', 'Other'])
            ]
        );
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
        $product = $this->productRepository->findOneByIdentifier('product_with_ref_entities');
        Assert::assertNotNull($product->getValue('a_reference_entity_attribute', null, null));

        // Update product with clear values
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('product_with_ref_entities'), userIntents: [
            new ClearValue('a_reference_entity_attribute', null, null),
            new ClearValue('a_reference_entity_collection_attribute', null, null),
        ]);

        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('product_with_ref_entities');
        Assert::assertNull($product->getValue('a_reference_entity_attribute', null, null));
        Assert::assertNull($product->getValue('a_reference_entity_collection_attribute', null, null));
    }

    /** @test */
    public function it_creates_a_product_with_a_boolean_value(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetBooleanValue('a_yes_no', null, null, true),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $this->assertProductHasCorrectValueByAttributeCode('a_yes_no', true);
    }

    /** @test */
    public function it_updates_a_product_with_a_boolean_value(): void
    {
        $this->updateProduct(new SetBooleanValue('a_yes_no', null, null, true));
        $this->assertProductHasCorrectValueByAttributeCode('a_yes_no', true);
    }

    /** @test */
    public function it_updates_a_product_with_a_simple_select_value(): void
    {
        $this->updateProduct(new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'));
        $this->assertProductHasCorrectValueByAttributeCode('a_simple_select', 'optionA');
    }

    /** @test */
    public function it_throws_an_exception_when_single_select_option_does_not_exist(): void
    {
        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('The toto value is not in the a_simple_select attribute option list.');

        $this->updateProduct(new SetSimpleSelectValue('a_simple_select', null, null, 'toto'));
    }

    /** @test */
    public function it_updates_a_product_with_a_multi_select_value(): void
    {
        $this->updateProduct(new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB']));
        $this->assertProductHasCorrectValueByAttributeCode('a_multi_select', ['optionA', 'optionB']);
    }

    /** @test */
    public function it_throws_an_exception_when_multi_select_option_does_not_exist(): void
    {
        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('The toto values are not in the a_multi_select attribute option list.');

        $this->updateProduct(new SetMultiSelectValue('a_multi_select', null, null, ['toto']));
        $this->updateProduct(new AddMultiSelectValue('a_multi_select', null, null, ['toto']));
    }

    /** @test */
    public function it_updates_a_product_with_an_add_multi_select_value(): void
    {
        $this->createAttributeOptions('a_multi_select', 'optionC', ['en_US' => 'C option']);

        $this->updateProduct(new AddMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB']));
        $this->assertProductHasCorrectValueByAttributeCode('a_multi_select', ['optionA', 'optionB']);

        $this->updateProduct(new AddMultiSelectValue('a_multi_select', null, null, ['optionB', 'optionC']));
        $this->assertProductHasCorrectValueByAttributeCode('a_multi_select', ['optionA', 'optionB', 'optionC']);
    }

    /** @test */
    public function it_throws_an_exception_when_two_intents_modify_the_same_value(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The value for attribute a_text is being updated multiple times');

        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetTextValue('a_text', null, null, 'foo'),
            new SetTextValue('a_text', null, null, 'bar'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_creates_a_product_with_a_date_value(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetDateValue('a_date', null, null, new \DateTime("2022-03-04T09:35:24")),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $this->assertProductHasCorrectValueByAttributeCode('a_date', new \DateTime("2022-03-04"));
    }

    /** @test */
    public function it_updates_a_product_with_a_date_value(): void
    {
        $this->updateProduct(new SetDateValue('a_date', null, null, new \DateTime("2022-03-04T09:35:24")));
        $this->assertProductHasCorrectValueByAttributeCode('a_date', new \DateTime("2022-03-04"));
    }

    /** @test */
    public function it_enables_and_disables_a_product(): void
    {
        $this->updateProduct(new SetEnabled(false));

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);

        Assert::assertFalse($product->isEnabled());

        $this->updateProduct(new SetEnabled(true));

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);

        Assert::assertTrue($product->isEnabled());
    }

    /** @test */
    public function it_can_set_and_remove_a_family_on_a_product(): void
    {
        $this->updateProduct(new SetFamily('familyA'));
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertSame('familyA', $product->getFamily()->getCode());

        $this->updateProduct(new SetFamily('familyA1'));
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertSame('familyA1', $product->getFamily()->getCode());

        $this->updateProduct(new RemoveFamily());
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertNull($product->getFamily());
    }

    /** @test */
    public function it_throws_an_exception_when_family_does_not_exist(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The unknown family does not exist in your PIM.');

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetFamily('unknown')]
        );
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_family_code_is_blank(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The family code requires a non empty string');

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetFamily('')]
        );
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_successfully_creates_a_product_with_a_record()
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_reference_entity_attribute',
                'type' => 'akeneo_reference_entity',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createRecords('brand', ['Akeneo']);

        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetSimpleReferenceEntityValue('a_reference_entity_attribute', null, null, 'Akeneo'),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $this->assertProductHasCorrectValueByAttributeCode('a_reference_entity_attribute', 'Akeneo');
    }

    /** @test */
    public function it_successfully_sets_a_product_record_code()
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_reference_entity_attribute',
                'type' => 'akeneo_reference_entity',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createRecords('brand', ['Akeneo', 'Ziggy']);

        $this->updateProduct(new SetSimpleReferenceEntityValue('a_reference_entity_attribute', null, null, 'Akeneo'));
        $this->assertProductHasCorrectValueByAttributeCode('a_reference_entity_attribute', 'Akeneo');
        $this->updateProduct(new SetSimpleReferenceEntityValue('a_reference_entity_attribute', null, null, 'Ziggy'));
        $this->assertProductHasCorrectValueByAttributeCode('a_reference_entity_attribute', 'Ziggy');
    }

    /** @test */
    public function it_throws_an_exception_when_record_does_not_exist()
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('Property "a_reference_entity_attribute" expects a valid record code. The record "Unknown" does not exist');

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_reference_entity_attribute',
                'type' => 'akeneo_reference_entity',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createRecords('brand', ['Akeneo', 'Ziggy']);

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetSimpleReferenceEntityValue('a_reference_entity_attribute', null, null, 'Unknown')]
        );
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_updates_a_product_categories(): void
    {
        $this->updateProduct(new SetCategories(['categoryA', 'categoryB']));

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);

        Assert::assertEqualsCanonicalizing(['categoryA', 'categoryB'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_throws_an_exception_when_category_doesnt_exist(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "toto, michel" categories do not exist');

        $this->updateProduct(new SetCategories(['toto', 'michel']));
    }

    /** @test */
    public function it_adds_categories_to_a_product(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['categoryA'])]
        );
        $this->messageBus->dispatch($command);
        $this->updateProduct(new AddCategories(['categoryA', 'categoryB', 'categoryC']));

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);

        Assert::assertEqualsCanonicalizing(['categoryA', 'categoryB', 'categoryC'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_throws_exception_when_trying_to_add_unexisting_category(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['categoryA'])]
        );
        $this->messageBus->dispatch($command);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "unknown" category does not exist');

        $this->updateProduct(new AddCategories(['categoryA', 'categoryB', 'unknown']));
    }

    /** @test */
    public function it_removes_categories_of_a_product(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['categoryA', 'categoryB', 'categoryC'])]
        );
        $this->messageBus->dispatch($command);

        $this->updateProduct(new RemoveCategories(['categoryA', 'categoryC']));
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing(['categoryB'], $product->getCategoryCodes());
    }

    /** @test */
    public function it_throws_exception_when_trying_to_remove_unexisting_categories(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetCategories(['categoryA', 'categoryB'])]
        );
        $this->messageBus->dispatch($command);

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "unknown" category does not exist');

        $this->updateProduct(new RemoveCategories(['categoryA', 'unknown']));
    }

    /** @test */
    public function it_successfully_creates_and_updates_a_product_with_a_set_multiple_records_value()
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_multi_reference_entity_attribute',
                'type' => 'akeneo_reference_entity_collection',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createRecords('brand', ['Akeneo', 'Ziggy', 'AnotherZiggy']);

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['Akeneo'])]
        );
        $this->messageBus->dispatch($command);

        $this->assertProductHasCorrectValueByAttributeCode(
            'a_multi_reference_entity_attribute',
            ['Akeneo']
        );
        $this->updateProduct(
            new SetMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['AnotherZiggy', 'Ziggy'])
        );
        $this->assertProductHasCorrectValueByAttributeCode(
            'a_multi_reference_entity_attribute',
            ['AnotherZiggy', 'Ziggy']
        );
    }

    /** @test */
    public function it_successfully_creates_and_updates_a_product_with_an_add_multiple_records_value()
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_multi_reference_entity_attribute',
                'type' => 'akeneo_reference_entity_collection',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createRecords('brand', ['Akeneo', 'Ziggy', 'AnotherZiggy']);

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new AddMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['Akeneo', 'Ziggy'])]
        );
        $this->messageBus->dispatch($command);

        $this->assertProductHasCorrectValueByAttributeCode(
            'a_multi_reference_entity_attribute',
            ['Akeneo', 'Ziggy']
        );
        $this->updateProduct(
            new AddMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['Ziggy', 'AnotherZiggy'])
        );
        $this->assertProductHasCorrectValueByAttributeCode(
            'a_multi_reference_entity_attribute',
            ['Akeneo', 'Ziggy', 'AnotherZiggy']
        );
    }

    /** @test */
    public function it_updates_a_product_with_a_remove_multiple_records_value()
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_multi_reference_entity_attribute',
                'type' => 'akeneo_reference_entity_collection',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createRecords('brand', ['Akeneo', 'Ziggy', 'AnotherZiggy']);

        $this->updateProduct(new SetMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['Akeneo', 'Ziggy', 'AnotherZiggy']));
        $this->assertProductHasCorrectValueByAttributeCode('a_multi_reference_entity_attribute', ['Akeneo', 'Ziggy', 'AnotherZiggy']);
        $this->updateProduct(new RemoveMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['Akeneo', 'AnotherZiggy']));
        $this->assertProductHasCorrectValueByAttributeCode('a_multi_reference_entity_attribute', ['Ziggy']);
    }

    /** @test */
    public function it_removes_last_record_code_with_a_remove_multiple_records_value()
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_multi_reference_entity_attribute',
                'type' => 'akeneo_reference_entity_collection',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );
        $this->createRecords('brand', ['Akeneo']);

        $this->updateProduct(new SetMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['Akeneo']));
        $this->assertProductHasCorrectValueByAttributeCode('a_multi_reference_entity_attribute', ['Akeneo']);
        $this->updateProduct(new RemoveMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['Akeneo']));

        $this->assertProductHasNoValueByAttributeCode('a_multi_reference_entity_attribute');
    }

    /** @test */
    public function it_throws_an_exception_when_multi_reference_entity_record_does_not_exist(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand');
        $this->createAttribute(
            [
                'code' => 'a_multi_reference_entity_attribute',
                'type' => 'akeneo_reference_entity_collection',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('Property "a_multi_reference_entity_attribute" expects valid record codes. The following records do not exist: "toto"');
        $this->updateProduct(new SetMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['toto']));

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('Property "a_multi_reference_entity_attribute" expects valid record codes. The following records do not exist: "toto"');
        $this->updateProduct(new AddMultiReferenceEntityValue('a_multi_reference_entity_attribute', null, null, ['toto']));
    }

    /** @test */
    public function it_can_modify_a_products_groups(): void
    {
        $this->updateProduct(new SetGroups(['groupA', 'groupB']));
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing(['groupA', 'groupB'], $product->getGroupCodes());

        $this->updateProduct(new RemoveFromGroups(['groupB']));
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing(['groupA'], $product->getGroupCodes());

        $this->updateProduct(new AddToGroups(['groupB']));
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing(['groupA', 'groupB'], $product->getGroupCodes());
    }

    /** @test */
    public function it_throws_an_exception_setting_an_unknown_group(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Property "groups" expects a valid group code. The group does not exist, "unknown" given.');

        $this->updateProduct(new SetGroups(['unknown', 'groupB']));
    }

    /** @test */
    public function it_creates_a_product_with_an_asset_value(): void
    {
        $this->loadAssetFixtures();

        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [
            new SetAssetValue('packshot_attr', null, null, ['packshot1'])
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $this->assertProductHasCorrectValueByAttributeCode('packshot_attr', ['packshot1']);
    }

    /** @test */
    public function it_updates_a_product_with_an_asset_value(): void
    {
        $this->loadAssetFixtures();

        $this->updateProduct(new SetAssetValue('packshot_attr', null, null, ['packshot1']));
        $this->assertProductHasCorrectValueByAttributeCode('packshot_attr', ['packshot1']);
    }


    /** @test */
    public function it_updates_a_product_with_an_add_asset_value(): void
    {
        $this->loadAssetFixtures();

        $this->updateProduct(new AddAssetValue('packshot_attr', null, null, ['packshot1', 'packshot2']));
        $this->assertProductHasCorrectValueByAttributeCode('packshot_attr', ['packshot1', 'packshot2']);

        $this->updateProduct(new AddAssetValue('packshot_attr', null, null, ['packshot2', 'packshot3']));
        $this->assertProductHasCorrectValueByAttributeCode('packshot_attr', ['packshot1', 'packshot2', 'packshot3']);
    }

    /** @test */
    public function it_throws_an_exception_when_asset_does_not_exist(): void
    {
        $this->loadAssetFixtures();

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('Please make sure the "toto" asset exists and belongs to the "packshot" asset family for the "packshot_attr" attribute.');

        $this->updateProduct(new SetAssetValue('packshot_attr', null, null, ['toto']));
        $this->updateProduct(new AddAssetValue('packshot_attr', null, null, ['toto']));
    }

    /** @test */
    public function it_update_a_product_with_a_remove_asset_value(): void
    {
        $this->loadAssetFixtures();

        $this->createProduct(
            'identifier',
            'familyA',
            [new SetAssetValue('packshot_attr', null, null, ['packshot1', 'packshot2', 'packshot3'])]
        );
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->updateProduct(new RemoveAssetValue('packshot_attr', null, null, ['packshot1', 'packshot2']));
        $this->assertProductHasCorrectValueByAttributeCode('packshot_attr', ['packshot3']);
    }

    /** @test */
    public function it_creates_and_updates_a_product_with_a_file_value(): void
    {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        // create product 'identifier'
        $aFilePath = $this->getFileInfoKey($this->getFixturePath('akeneo.pdf'));
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetFileValue('a_file', null, null, $aFilePath)]
        );
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEquals($aFilePath, $product->getValue('a_file')->getData()->getKey());

        // update product 'identifier'
        $anotherFilePath = $this->getFileInfoKey($this->getFixturePath('akeneo.txt'));
        $this->updateProduct(
            new SetFileValue('a_file', null, null, $anotherFilePath)
        );

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEquals($anotherFilePath, $product->getValue('a_file')->getData()->getKey());
    }

    /** @test */
    public function it_throws_an_exception_when_file_extension_is_wrong(): void
    {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('The png file extension is not allowed for the a_file attribute. Allowed extensions are pdf, doc, docx, txt.');

        // create product 'identifier'
        $anImagePath = $this->getFileInfoKey($this->getFixturePath('akeneo.png'));
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetFileValue('a_file', null, null, $anImagePath)]
        );
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
    }

    /** @test */
    public function it_creates_and_updates_a_product_with_an_image_value(): void
    {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        // create product 'identifier'
        $anImagePath = $this->getFileInfoKey($this->getFixturePath('akeneo.png'));
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetImageValue('an_image', null, null, $anImagePath)]
        );
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEquals($anImagePath, $product->getValue('an_image')->getData()->getKey());

        // update product 'identifier'
        $anotherFilePath = $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'));
        $this->updateProduct(
            new SetImageValue('an_image', null, null, $anotherFilePath)
        );

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEquals($anotherFilePath, $product->getValue('an_image')->getData()->getKey());
    }

    /** @test */
    public function it_throws_an_exception_when_image_extension_is_wrong(): void
    {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('The txt file extension is not allowed for the an_image attribute. Allowed extensions are jpg, gif, png.');

        // create product 'identifier'
        $anImagePath = $this->getFileInfoKey($this->getFixturePath('akeneo.txt'));
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [new SetImageValue('an_image', null, null, $anImagePath)]
        );
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
    }

    /** @test */
    public function it_create_a_product_with_a_price_collection_value(): void
    {
        $this->updateProduct(new SetPriceCollectionValue('a_price', null, null, [
            new PriceValue('42', 'EUR'),
            new PriceValue('24', 'USD'),
        ]));

        $this->assertProductHasCorrectValueByAttributeCode(
            'a_price',
            new PriceCollection([
                new ProductPrice('42.00', 'EUR'),
                new ProductPrice('24.00', 'USD'),
            ])
        );
    }

    /** @test */
    public function it_updates_a_product_with_a_price_value(): void
    {
        $this->updateProduct(new SetPriceValue('a_price', null, null, new PriceValue('42', 'EUR')));
        $this->assertProductHasCorrectValueByAttributeCode(
            'a_price',
            new PriceCollection([
                new ProductPrice('42.00', 'EUR')
            ])
        );
    }

    /** @test */
    public function it_updates_a_product_with_an_add_price_value(): void
    {
        $this->updateProduct(new SetPriceValue('a_price', null, null, new PriceValue('42', 'EUR')));
        $this->assertProductHasCorrectValueByAttributeCode(
            'a_price',
            new PriceCollection([
                new ProductPrice('42.00', 'EUR')
            ])
        );

        $this->updateProduct(new SetPriceValue('a_price', null, null, new PriceValue('50', 'USD')));
        $this->assertProductHasCorrectValueByAttributeCode(
            'a_price',
            new PriceCollection([
                new ProductPrice('42.00', 'EUR'),
                new ProductPrice('50.00', 'USD'),
            ]),
        );

        $this->updateProduct(new SetPriceValue('a_price', null, null, new PriceValue('50', 'EUR')));
        $this->assertProductHasCorrectValueByAttributeCode(
            'a_price',
            new PriceCollection([
                new ProductPrice('50.00', 'EUR'),
                new ProductPrice('50.00', 'USD'),
            ]),
        );
    }

    /** @test */
    public function it_creates_a_price_value_on_a_product(): void
    {
        $this->updateProduct(new SetPriceCollectionValue('a_price', null, null, [
            new PriceValue('42', 'EUR'),
            new PriceValue('24', 'USD'),
        ]));

        $this->assertProductHasCorrectValueByAttributeCode(
            'a_price',
            new PriceCollection([new ProductPrice('42.00', 'EUR'), new ProductPrice('24.00', 'USD')])
        );

        $this->updateProduct(new ClearPriceValue('a_price', null, null, 'EUR'));

        $this->assertProductHasCorrectValueByAttributeCode(
            'a_price',
            new PriceCollection([new ProductPrice('24.00', 'USD')])
        );
    }

    /** @test */
    public function it_throws_an_exception_when_currency_does_not_exist(): void
    {
        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('Please specify a valid currency for the a_price attribute, the UNKNOWN code was sent.');

        $this->updateProduct(new SetPriceCollectionValue('a_price', null, null, [
            new PriceValue('42', 'UNKNOWN'),
        ]));
    }

    /** @test */
    public function it_creates_and_updates_a_product_with_a_table_value(): void
    {
        FeatureHelper::skipIntegrationTestWhenTableAttributeIsNotActivated();

        $this->createAttribute([
            'code' => 'a_table',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        // create product 'identifier'
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [
                new SetTableValue(
                    'a_table',
                    null,
                    null,
                    [
                        ['ingredient' => 'salt'],
                        ['ingredient' => 'egg', 'quantity' => 2],
                    ]
                )
            ]
        );
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();

        $tableNormalizer = $this->get('Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard\TableNormalizer');
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEquals(
            [
                ['ingredient' => 'salt'],
                ['ingredient' => 'egg', 'quantity' => 2],
            ],
            $tableNormalizer->normalize($product->getValue('a_table')->getData())
        );

        // update product 'identifier'
        $this->updateProduct(
            new SetTableValue(
                'a_table',
                null,
                null,
                [
                    ['ingredient' => 'butter', 'quantity' => 3],
                    ['ingredient' => 'egg'],
                ]
            )
        );

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEquals(
            [
                ['ingredient' => 'butter', 'quantity' => 3],
                ['ingredient' => 'egg'],
            ],
            $tableNormalizer->normalize($product->getValue('a_table')->getData())
        );
    }

    /** @test */
    public function it_throws_an_exception_when_table_value_has_unknown_column(): void
    {
        FeatureHelper::skipIntegrationTestWhenTableAttributeIsNotActivated();

        $this->createAttribute([
            'code' => 'a_table',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('The "origin" column does not exist');

        // create product 'identifier'
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [
                new SetTableValue(
                    'a_table',
                    null,
                    null,
                    [['ingredient' => 'butter', 'origin' => 'Nantes', 'quantity' => 3]]
                )
            ]
        );
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_creates_and_updates_a_product_with_a_simple_reference_data_value(): void
    {
        $this->createAttribute([
            'code' => 'color_ref_data',
            'type' => AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'reference_data_name' => 'color',
        ]);

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        // create product 'identifier'
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [
                new SetSimpleReferenceDataValue('color_ref_data', null, null, 'red'),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertNotNull($product->getValue('color_ref_data'));
        Assert::assertEquals(
            'red',
            $product->getValue('color_ref_data')->getData()
        );

        $this->updateProduct(new SetSimpleReferenceDataValue('color_ref_data', null, null, 'yellow'));

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEquals(
            'yellow',
            $product->getValue('color_ref_data')->getData()
        );
    }

    /** @test */
    public function it_creates_and_updates_a_product_with_a_multi_reference_data_value(): void
    {
        $this->createAttribute([
            'code' => 'color_ref_data',
            'type' => AttributeTypes::REFERENCE_DATA_MULTI_SELECT,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'reference_data_name' => 'color',
        ]);

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        // create product 'identifier'
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier('identifier'),
            userIntents: [
                new SetMultiReferenceDataValue('color_ref_data', null, null, ['red', 'yellow']),
            ]
        );
        $this->messageBus->dispatch($command);
        $this->clearDoctrineUoW();

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertNotNull($product->getValue('color_ref_data'));
        Assert::assertEqualsCanonicalizing(
            ['red', 'yellow'],
            $product->getValue('color_ref_data')->getData()
        );

        $this->updateProduct(new SetMultiReferenceDataValue('color_ref_data', null, null, ['red', 'blue']));

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        Assert::assertEqualsCanonicalizing(
            ['red', 'blue'],
            $product->getValue('color_ref_data')->getData()
        );
    }

    /** @test */
    public function it_creates_an_empty_product_with_given_uuid(): void
    {
        $uuid = Uuid::uuid4();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNull($product);

        $productUuid = ProductUuid::fromUuid($uuid);
        $command = UpsertProductCommand::createWithUuid($this->getUserId('admin'), $productUuid, [
            new SetIdentifierValue('sku', 'product_sku'),
        ]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->find($uuid);
        Assert::assertNotNull($product);
        Assert::assertSame('product_sku', $product->getIdentifier());
        Assert::assertSame($uuid->toString(), $product->getUuid()->toString());
    }

    /** @test */
    public function it_creates_a_product_without_identifier_nor_uuid_as_param()
    {
        $product = $this->productRepository->findOneByIdentifier('product_sku');
        Assert::assertNull($product);

        $command = UpsertProductCommand::createWithoutUuidNorIdentifier(
            userId: $this->getUserId('admin'),
            userIntents: [new SetIdentifierValue('sku', 'product_sku')]
        );
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('product_sku');
        Assert::assertNotNull($product);
        Assert::assertSame('product_sku', $product->getIdentifier());
    }

    /**
     * @test
     */
    public function it_can_create_a_product_without_identifier_value(): void
    {
        $productCount = $this->productRepository->countAll();
        $this->messageBus->dispatch(UpsertProductCommand::createWithoutUuidNorIdentifier(
            userId: $this->getUserId('admin'),
            userIntents: [new SetEnabled(false)]
        ));
        Assert::assertEquals($productCount + 1, $this->productRepository->countAll());
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }

    private function loadAssetFixtures(): void
    {
        FeatureHelper::skipIntegrationTestWhenAssetFeatureIsNotActivated();
        $this->get('feature_flags')->enable('asset_manager');

        ($this->get('akeneo_assetmanager.application.asset_family.create_asset_family_handler'))(
            /** @phpstan-ignore-next-line */
            new CreateAssetFamilyCommand('packshot', ['en_US' => 'Packshot'])
        );

        ($this->get('akeneo_assetmanager.application.asset.create_asset_handler'))(
            /** @phpstan-ignore-next-line */
            new CreateAssetCommand('packshot', 'packshot1', ['en_US' => 'Packshot 1'])
        );
        ($this->get('akeneo_assetmanager.application.asset.create_asset_handler'))(
            /** @phpstan-ignore-next-line */
            new CreateAssetCommand('packshot', 'packshot2', ['en_US' => 'Packshot 2'])
        );
        ($this->get('akeneo_assetmanager.application.asset.create_asset_handler'))(
            /** @phpstan-ignore-next-line */
            new CreateAssetCommand('packshot', 'packshot3', ['en_US' => 'Packshot 3'])
        );

        $this->createAttribute(
            [
                'code' => 'packshot_attr',
                'type' => 'pim_catalog_asset_collection',
                'group' => 'other',
                'reference_data_name' => 'packshot',
            ]
        );
    }

    private function assertProductHasCorrectValueByAttributeCode(
        string $attributeCode,
        mixed $expectedValue
    ): void {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue($attributeCode, null, null);
        Assert::assertNotNull($value);
        Assert::assertEquals($expectedValue, $value->getData());
    }

    private function assertProductHasNoValueByAttributeCode(string $attributeCode): void
    {
        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $value = $product->getValue($attributeCode, null, null);
        Assert::assertNull($value);
    }

    private function createAttribute(array $data): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $attributeViolations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $attributeViolations, \sprintf('The attribute is invalid: %s', $attributeViolations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function updateProduct(UserIntent $userIntent): void
    {
        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('identifier');
        if (null === $product) {
            // Creates empty product
            $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: []);
            $this->messageBus->dispatch($command);
            $product = $this->productRepository->findOneByIdentifier('identifier');
            Assert::assertNotNull($product);
        }

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        // Update product with userIntent value
        $command = UpsertProductCommand::createWithIdentifier(userId: $this->getUserId('admin'), productIdentifier: ProductIdentifier::fromIdentifier('identifier'), userIntents: [$userIntent]);
        $this->messageBus->dispatch($command);

        $this->clearDoctrineUoW();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, ?string $familyCode, array $userIntents): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: [
                new SetFamily($familyCode),
                ...$userIntents
            ]
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
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

    private function createAttributeOptions(string $attributeCode, string $optionCode, array $labels): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => $labels,
        ]);
        $constraints = $this->get('validator')->validate($attributeOption);
        if (count($constraints) > 0) {
            throw new \InvalidArgumentException((string)$constraints);
        }
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    private function createReferenceEntity(string $referenceEntityCode): void
    {
        $this->get('feature_flags')->enable('reference_entity');

        /** @phpstan-ignore-next-line */
        $createReferenceEntityCommand = new CreateReferenceEntityCommand($referenceEntityCode, []);
        $validator = $this->get('validator');
        $violations = $validator->validate($createReferenceEntityCommand);
        Assert::assertCount(0, $violations, \sprintf('The command is not valid: %s', $violations));
        ($this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler'))(
            $createReferenceEntityCommand
        );
    }

    private function createRecords(string $referenceEntityCode, array $recordCodes): void
    {
        $validator = $this->get('validator');
        foreach ($recordCodes as $recordCode) {
            /** @phpstan-ignore-next-line */
            $createRecord = new CreateRecordCommand($referenceEntityCode, $recordCode, []);
            $violations = $validator->validate($createRecord);
            Assert::assertCount(0, $violations, \sprintf('The command is not valid: %s', $violations));
            ($this->get('akeneo_referenceentity.application.record.create_record_handler'))($createRecord);
        }
    }
}
