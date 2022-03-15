<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMetricValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
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
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
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
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
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
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
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
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
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
    public function it_throws_an_exception_when_a_metric_amount_is_not_numeric(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('This value should be of type numeric.');
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
            new SetMetricValue('a_metric', null, null, 'michel', 'KILOWATT'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_a_metric_unit_is_unknown(): void
    {
        $this->expectException(LegacyViolationsException::class);
        $this->expectExceptionMessage('Please specify a valid metric unit');
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
            new SetMetricValue('a_metric', null, null, '1275', 'unknown'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_a_locale_for_a_non_localizable_product(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The a_text attribute does not require a locale, "en_US" was detected');

        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
            new SetTextValue('a_text', null, 'en_US', 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_an_unknown_user(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "0" user does not exist');

        $command = new UpsertProductCommand(userId: 0, productIdentifier: 'identifier', valueUserIntents: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_giving_an_empty_product_identifier(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The product identifier requires a non empty string');

        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: '', valueUserIntents: [
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
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
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
        FeatureHelper::skipIntegrationTestWhenAssetFeatureIsNotActivated();

        ($this->get('akeneo_assetmanager.application.asset_family.create_asset_family_handler'))(
            /** @phpstan-ignore-next-line */
            new CreateAssetFamilyCommand('packshot', ['en_US' => 'Packshot'])
        );

        ($this->get('akeneo_assetmanager.application.asset.create_asset_handler'))(
            /** @phpstan-ignore-next-line */
            new CreateAssetCommand('packshot', 'packshot1', ['en_US' => 'Packshot 1'])
        );

        $this->createAttribute(
            [
                'code' => 'packshot_attr',
                'type' => 'pim_catalog_asset_collection',
                'group' => 'other',
                'reference_data_name' => 'packshot',
            ]
        );

        $this->createProduct(
            'product_with_asset',
            'other',
            ['packshot_attr' => [['scope' => null, 'locale' => null, 'data' => ['packshot1']]]]
        );
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $product = $this->productRepository->findOneByIdentifier('product_with_asset');
        Assert::assertNotNull($product->getValue('packshot_attr', null, null));

        // Update product with clear values
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'product_with_asset', valueUserIntents: [
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

        /** @phpstan-ignore-next-line */
        $createBrandCommand = new CreateReferenceEntityCommand('brand', []);
        $validator = $this->get('validator');
        $violations = $validator->validate($createBrandCommand);
        Assert::assertCount(0, $violations, \sprintf('The command is not valid: %s', $violations));
        ($this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler'))(
            $createBrandCommand
        );

        /** @phpstan-ignore-next-line */
        $createAkeneoRecord = new CreateRecordCommand('brand', 'Akeneo', []);
        $violations = $validator->validate($createAkeneoRecord);
        Assert::assertCount(0, $violations, \sprintf('The command is not valid: %s', $violations));
        ($this->get('akeneo_referenceentity.application.record.create_record_handler'))($createAkeneoRecord);

        /** @phpstan-ignore-next-line */
        $createOtherRecord = new CreateRecordCommand('brand', 'Other', []);
        $violations = $validator->validate($createOtherRecord);
        Assert::assertCount(0, $violations, \sprintf('The command is not valid: %s', $violations));
        ($this->get('akeneo_referenceentity.application.record.create_record_handler'))($createOtherRecord);

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
            'other',
            [
                'a_reference_entity_attribute' => [['scope' => null, 'locale' => null, 'data' => 'Akeneo']],
                'a_reference_entity_collection_attribute' => [['scope' => null, 'locale' => null, 'data' => ['Akeneo', 'Other']]]
            ]
        );
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
        $product = $this->productRepository->findOneByIdentifier('product_with_ref_entities');
        Assert::assertNotNull($product->getValue('a_reference_entity_attribute', null, null));

        // Update product with clear values
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'product_with_ref_entities', valueUserIntents: [
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
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
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

        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
            new SetTextValue('a_text', null, null, 'foo'),
            new SetTextValue('a_text', null, null, 'bar'),
        ]);
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_creates_a_product_with_a_date_value(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier', valueUserIntents: [
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

        $command = new UpsertProductCommand(
            userId: $this->getUserId('admin'),
            productIdentifier: 'identifier',
            familyUserIntent: new SetFamily('unknown')
        );
        $this->messageBus->dispatch($command);
    }

    /** @test */
    public function it_throws_an_exception_when_family_code_is_blank(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The family code requires a non empty string');

        $command = new UpsertProductCommand(
            userId: $this->getUserId('admin'),
            productIdentifier: 'identifier',
            familyUserIntent: new SetFamily('')
        );
        $this->messageBus->dispatch($command);
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
        Assert::assertEquals($expectedValue, $value->getData());
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
            $command = new UpsertProductCommand(userId: $this->getUserId('admin'), productIdentifier: 'identifier');
            $this->messageBus->dispatch($command);
            $product = $this->productRepository->findOneByIdentifier('identifier');
            Assert::assertNotNull($product);
        }

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        // Update product with userIntent value
        $command = UpsertProductCommand::createFromCollection(userId: $this->getUserId('admin'), productIdentifier: 'identifier', userIntents: [$userIntent]);
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
}
