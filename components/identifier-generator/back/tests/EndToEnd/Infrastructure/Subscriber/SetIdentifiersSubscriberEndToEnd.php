<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\EndToEndTestCase;
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

    private function createIdentifierGenerator(?array $conditions = []): void
    {
        ($this->getCreateGeneratorHandler())(new CreateGeneratorCommand(
            'my_generator',
            $conditions,
            [
                ['type' => 'free_text', 'string' => 'AKN'],
                ['type' => 'auto_number', 'numberMin' => 50, 'digitsMin' => 3],
            ],
            ['en_US' => 'My Generator'],
            'sku',
            '-'
        ));
    }

    /** @test */
    public function it_should_generate_an_identifier_on_create(): void
    {
        $this->createIdentifierGenerator();
        $productFromDatabase = $this->createProduct();

        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-050', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_several_identifiers_on_create(): void
    {
        $this->createIdentifierGenerator();
        $productsFromDatabase = $this->createProducts(5);
        ;

        Assert::assertSame(
            ['AKN-050', 'AKN-051', 'AKN-052', 'AKN-053', 'AKN-054'],
            \array_map(fn (ProductInterface $product): ?string => $product->getIdentifier(), $productsFromDatabase)
        );
    }

    /** @test */
    public function it_should_generate_an_identifier_when_deleting_previous_identifier(): void
    {
        $this->createIdentifierGenerator();
        $product = $this->createProduct('originalIdentifier');
        $productFromDatabase = $this->updateProductIdentifier($product, null);

        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-050', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_the_next_identifier_if_there_is_already_one_created(): void
    {
        $this->createIdentifierGenerator();
        $this->createProduct('AKN-050');

        $productFromDatabase = $this->createProduct();
        Assert::assertSame('AKN-051', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-051', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_not_generate_the_identifier_if_generated_value_is_invalid(): void
    {
        $this->createIdentifierGenerator();
        $this->addRestrictionsOnIdentifierAttribute();

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
        $this->createIdentifierGenerator([
            ['type' => 'family', 'operator' => 'NOT EMPTY'],
        ]);

        $this->createProduct();
        $productFromDatabase = $this->createProduct();
        Assert::assertSame(null, $productFromDatabase->getIdentifier());

        $this->createFamily('tshirt');
        $this->setProductFamily($productFromDatabase->getUuid(), 'tshirt');
        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
    }

    /** @test */
    public function it_should_generate_identifier_on_product_values_update(): void
    {
        $this->createSimpleSelectAttributeWithOption('color');
        $this->createIdentifierGenerator([
            ['type' => 'simple_select', 'operator' => 'IN', 'attributeCode' => 'color', 'value' => ['red']],
        ]);

        $this->createProduct();
        $productFromDatabase = $this->createProduct();
        Assert::assertSame(null, $productFromDatabase->getIdentifier());

        $this->setSimpleSelectProductValue($productFromDatabase->getUuid());
        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
    }

    private function addRestrictionsOnIdentifierAttribute(): void
    {
        $this->getConnection()->executeQuery(<<<SQL
UPDATE pim_catalog_attribute SET max_characters=1 WHERE code='sku';
SQL);
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
            '-'
        ));
    }

    private function createFamily(string $familyCode): FamilyInterface
    {
        $family = $this->getFamilyFactory()->create();
        $this->getFamilyUpdater()->update($family, ['code' => $familyCode]);
        $familyViolations = $this->getValidator()->validate($family);
        $this->assertCount(0, $familyViolations);
        $this->getFamilySaver()->save($family);

        return $family;
    }

    private function createSimpleSelectAttributeWithOption(string $attributeCode): void
    {
        $attribute = $this->getAttributeFactory()->create();
        $this->getAttributeUpdater()->update($attribute, [
            'code' => $attributeCode,
            'type' => 'pim_catalog_simpleselect',
            'group' => 'other',
        ]);
        $attributeViolations = $this->getValidator()->validate($attribute);
        $this->assertCount(0, $attributeViolations);
        $this->getAttributeSaver()->save($attribute);

        $attributeOption = new AttributeOption();
        $attributeOption->setCode('red');
        $attributeOption->setAttribute($attribute);
        $attributeOptionViolations = $this->getValidator()->validate($attributeOption);
        $this->assertCount(0, $attributeOptionViolations);
        $this->getAttributeOptionSaver()->save($attributeOption);
    }

    private function getCreateGeneratorHandler(): CreateGeneratorHandler
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getFamilyFactory(): FamilyFactory
    {
        return $this->get('pim_catalog.factory.family');
    }

    private function getFamilyUpdater(): FamilyUpdater
    {
        return $this->get('pim_catalog.updater.family');
    }

    private function getFamilySaver(): FamilySaver
    {
        return $this->get('pim_catalog.saver.family');
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
