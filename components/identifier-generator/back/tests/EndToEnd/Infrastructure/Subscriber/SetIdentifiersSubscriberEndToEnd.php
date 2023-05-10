<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\EndToEndTestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetIdentifiersSubscriberEndToEnd extends EndToEndTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog(['identifier_generator']);
    }

    /** @test */
    public function it_should_generate_an_identifier_on_create(): void
    {
        $this->createIdentifierGenerator(
            code: 'not_executed_one',
            conditions: [['type' => 'family', 'operator' => 'EMPTY']],
            structure: [['type' => 'free_text', 'string' => 'not_executed_one']]
        );
        $this->createIdentifierGenerator();
        $productFromDatabase = $this->createProduct();

        Assert::assertSame('akn-050-my_family', $productFromDatabase->getIdentifier());
        Assert::assertSame('akn-050-my_family', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_an_identifier_with_simple_select_on_create(): void
    {
        $this->createSimpleSelectAttributeWithOption('brand', true, true, 'akeneo');
        $this->createSimpleSelectAttributeWithOption('clothing_size', false, false, 'medium');
        $this->createIdentifierGenerator(structure:[
            [
                'type' => 'simple_select',
                'attributeCode' => 'brand',
                'process' => ['type' => 'no'],
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
            [
                'type' => 'simple_select',
                'attributeCode' => 'clothing_size',
                'process' => ['type' => 'no'],
            ], ]);
        $product = $this->createProduct(userIntents: [
            new SetSimpleSelectValue('brand', 'ecommerce', 'en_US', 'akeneo'),
            new SetSimpleSelectValue('clothing_size', null, null, 'medium'),
        ]);

        Assert::assertSame('akeneo-medium-akn-050-my_family', $product->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_an_identifier_with_a_reference_entity_on_create(): void
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

        $this->createReferenceEntity('colors');
        $this->createRecords('colors', ['blue', 'white']);

        $this->createAttribute(
            [
                'code' => 'a_second_reference_entity_attribute',
                'type' => 'akeneo_reference_entity',
                'group' => 'other',
                'reference_data_name' => 'colors',
            ]
        );

        $this->createNomenclature('a_second_reference_entity_attribute', [
            'blue' => 'blu',
            'white' => 'whi',
        ]);

        $this->createIdentifierGenerator(structure:[
            [
                'type' => 'reference_entity',
                'attributeCode' => 'a_reference_entity_attribute',
                'process' => ['type' => 'no'],
            ],
            [
                'type' => 'reference_entity',
                'attributeCode' => 'a_second_reference_entity_attribute',
                'process' => ['type' => Process::PROCESS_TYPE_NOMENCLATURE]
            ]
        ]);
        $product = $this->createProduct(userIntents: [
            new SetSimpleReferenceEntityValue('a_reference_entity_attribute', null, null, 'akeneo'),
            new SetSimpleReferenceEntityValue('a_second_reference_entity_attribute', null, null, 'blue'),
        ]);

        Assert::assertSame('akeneo-blu-akn-050-my_family', $product->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_several_identifiers_on_create(): void
    {
        $this->createIdentifierGenerator();
        $productsFromDatabase = $this->createProducts(5);

        Assert::assertSame(
            ['akn-050-my_family', 'akn-051-my_family', 'akn-052-my_family', 'akn-053-my_family', 'akn-054-my_family'],
            \array_map(fn (ProductInterface $product): ?string => $product->getIdentifier(), $productsFromDatabase)
        );
    }

    /** @test */
    public function it_should_generate_an_identifier_when_deleting_previous_identifier(): void
    {
        $this->createIdentifierGenerator();
        $product = $this->createProduct('originalIdentifier');
        $productFromDatabase = $this->updateProductIdentifier($product, null);

        Assert::assertSame('akn-050-my_family', $productFromDatabase->getIdentifier());
        Assert::assertSame('akn-050-my_family', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_the_next_identifier_if_there_is_already_one_created(): void
    {
        $this->createIdentifierGenerator();
        $this->createProduct('AKN-050');

        $productFromDatabase = $this->createProduct();
        Assert::assertSame('akn-051-my_family', $productFromDatabase->getIdentifier());
        Assert::assertSame('akn-051-my_family', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_not_generate_the_identifier_if_generated_value_is_invalid(): void
    {
        $this->addRestrictionsOnIdentifierAttribute();
        $this->createIdentifierGenerator();
        $this->createIdentifierGenerator(code: 'another_generator');

        $productFromDatabase = $this->createProduct();
        Assert::assertSame(null, $productFromDatabase->getIdentifier());
        Assert::assertNull($productFromDatabase->getValue('sku'));
    }

    /** @test */
    public function it_should_not_generate_the_identifier_if_generated_product_contains_invalid_character(): void
    {
        $this->createIdentifierGeneratorWithFreeTextValue(',');

        $productFromDatabase = $this->createProduct();
        Assert::assertSame(null, $productFromDatabase->getIdentifier());
        Assert::assertNull($productFromDatabase->getValue('sku'));
    }

    /** @test */
    public function it_should_not_generate_the_identifier_if_generated_product_is_too_long(): void
    {
        $exceptionMsg = '';

        try {
            $this->createIdentifierGeneratorWithFreeTextValue(\str_repeat('a', 257));
        } catch (ViolationsException $exception) {
            $exceptionMsg = $exception->getMessage();
        }
        Assert::assertSame('structure[0][string]: This value is too long. It should have 100 characters or less.', $exceptionMsg);

        $productFromDatabase = $this->createProduct();
        Assert::assertSame(null, $productFromDatabase->getIdentifier());
        Assert::assertNull($productFromDatabase->getValue('sku'));
    }

    /** @test */
    public function it_should_generate_identifier_on_family_update(): void
    {
        $this->createIdentifierGenerator(
            conditions: [['type' => 'family', 'operator' => 'NOT EMPTY']]
        );

        $productFromDatabase = $this->createProduct(withFamily: false);
        Assert::assertSame(null, $productFromDatabase->getIdentifier());

        $this->createFamily('tshirt');
        $this->setProductFamily($productFromDatabase->getUuid(), 'tshirt');

        $productUpdate = $this->getProductRepository()->find($productFromDatabase->getUuid());
        Assert::assertSame('akn-050-tshirt', $productUpdate->getIdentifier());
    }

    /** @test */
    public function it_should_generate_identifier_on_product_values_update(): void
    {
        $this->createSimpleSelectAttributeWithOption('color');
        $this->createIdentifierGenerator(
            conditions: [['type' => 'simple_select', 'operator' => 'IN', 'attributeCode' => 'color', 'value' => ['red']]]
        );

        $this->createProduct();
        $productFromDatabase = $this->createProduct();
        Assert::assertSame(null, $productFromDatabase->getIdentifier());

        $this->setSimpleSelectProductValue($productFromDatabase->getUuid());

        $productUpdate = $this->getProductRepository()->find($productFromDatabase->getUuid());
        Assert::assertSame('akn-050-my_family', $productUpdate->getIdentifier());
    }

    private function createIdentifierGenerator(
        ?string $code = null,
        array $conditions = [],
        array $structure = [],
    ): void {
        ($this->getCreateGeneratorHandler())(new CreateGeneratorCommand(
            $code ?? 'my_generator',
            $conditions,
            \array_merge($structure, [
                ['type' => 'free_text', 'string' => 'AKN'],
                ['type' => 'auto_number', 'numberMin' => 50, 'digitsMin' => 3],
                ['type' => 'family', 'process' => ['type' => 'no']],
            ]),
            ['en_US' => 'My Generator'],
            'sku',
            '-',
            'lowercase',
        ));
    }

    private function addRestrictionsOnIdentifierAttribute(): void
    {
        $this->getConnection()->executeQuery(<<<SQL
UPDATE pim_catalog_attribute SET max_characters=1 WHERE code='sku';
SQL);
        $this->get('doctrine.orm.entity_manager')->clear();
    }

    private function createIdentifierGeneratorWithFreeTextValue(string $value): void
    {
        ($this->getCreateGeneratorHandler())(new CreateGeneratorCommand(
            'my_generator',
            [],
            [
                ['type' => 'free_text', 'string' => $value],
                ['type' => 'auto_number', 'numberMin' => 50, 'digitsMin' => 3],
            ],
            ['en_US' => 'My Generator'],
            'sku',
            '-',
            'no',
        ));
    }

    private function createSimpleSelectAttributeWithOption(string $attributeCode, bool $localizable = false, bool $scopable = false, string $optionCode = 'red'): void
    {
        $attribute = $this->getAttributeFactory()->create();
        $this->getAttributeUpdater()->update($attribute, [
            'code' => $attributeCode,
            'type' => 'pim_catalog_simpleselect',
            'group' => 'other',
            'localizable' => $localizable,
            'scopable' => $scopable,
        ]);
        $attributeViolations = $this->getValidator()->validate($attribute);
        $this->assertCount(0, $attributeViolations);
        $this->getAttributeSaver()->save($attribute);

        $attributeOption = new AttributeOption();
        $attributeOption->setCode($optionCode);
        $attributeOption->setAttribute($attribute);
        $attributeOptionViolations = $this->getValidator()->validate($attributeOption);
        $this->assertCount(0, $attributeOptionViolations);
        $this->getAttributeOptionSaver()->save($attributeOption);
    }

    private function createAttribute(array $data): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $attributeViolations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $attributeViolations, \sprintf('The attribute is invalid: %s', $attributeViolations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
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

    private function createNomenclature(string $propertyCode, array $values): void
    {
        $command = new UpdateNomenclatureCommand($propertyCode, '=', 3, false, $values);

        ($this->getUpdateNomenclatureHandler())($command);
    }

    private function getUpdateNomenclatureHandler(): UpdateNomenclatureHandler
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler');
    }

    private function getCreateGeneratorHandler(): CreateGeneratorHandler
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getAttributeFactory(): AttributeFactory
    {
        return $this->get('pim_catalog.factory.attribute');
    }

    private function getAttributeUpdater(): AttributeUpdater
    {
        return $this->get('pim_catalog.updater.attribute');
    }

    private function getValidator(): ValidatorInterface
    {
        return $this->get('validator');
    }

    private function getAttributeSaver(): AttributeSaver
    {
        return $this->get('pim_catalog.saver.attribute');
    }

    private function getAttributeOptionSaver(): BaseSaver
    {
        return $this->get('pim_catalog.saver.attribute_option');
    }
}
