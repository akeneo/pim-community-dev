<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class SetIdentifiersSubscriberEndToEnd extends TestCase
{
    private UserInterface $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createDefaultIdentifierGenerator(): void
    {
        ($this->getCreateGeneratorHandler())(new CreateGeneratorCommand(
            'my_generator',
            [],
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
        $this->createDefaultIdentifierGenerator();
        $productFromDatabase = $this->createProduct();

        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-050', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_an_identifier_when_deleting_previous_identifier(): void
    {
        $this->createDefaultIdentifierGenerator();
        $product = $this->createProduct('originalIdentifier');
        $productFromDatabase = $this->setIdentifier($product, null);

        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-050', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_the_next_identifier_if_there_is_already_one_created(): void
    {
        $this->createDefaultIdentifierGenerator();
        $this->createProduct('AKN-050');

        $productFromDatabase = $this->createProduct();
        Assert::assertSame('AKN-051', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-051', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_not_generate_the_identifier_if_generated_value_is_invalid(): void
    {
        $this->createDefaultIdentifierGenerator();
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

    private function createProduct(?string $identifier = null): ProductInterface
    {
        $uuid = Uuid::uuid4();
        $this->getAuthenticator()->logIn('admin');

        $userIntents = [];
        if (null !== $identifier) {
            $userIntents = [new SetIdentifierValue('sku', $identifier)];
        }

        $command = UpsertProductCommand::createWithUuid(
            $this->admin->getId(),
            ProductUuid::fromUuid($uuid),
            $userIntents
        );
        ($this->getUpsertProductHandler())($command);

        return $this->getProductRepository()->find($uuid);
    }

    private function setIdentifier(ProductInterface $product, ?string $identifier = null): ProductInterface
    {
        $command = UpsertProductCommand::createWithUuid(
            $this->admin->getId(),
            ProductUuid::fromUuid($product->getUuid()),
            [
                new SetIdentifierValue('sku', $identifier),
            ]
        );
        ($this->getUpsertProductHandler())($command);

        return $this->getProductRepository()->find($product->getUuid());
    }

    private function addRestrictionsOnIdentifierAttribute(): void
    {
        $this->getConnection()->executeQuery(<<<SQL
UPDATE pim_catalog_attribute SET max_characters=1 WHERE code='sku';
SQL);
    }

    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
    }

    private function getCreateGeneratorHandler(): CreateGeneratorHandler
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler');
    }

    private function getUpsertProductHandler(): UpsertProductHandler
    {
        return $this->get('Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler');
    }

    private function getAuthenticator(): AuthenticatorHelper
    {
        return $this->get('akeneo_integration_tests.helper.authenticator');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
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
}
