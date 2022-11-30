<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Subscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SqlUpdateIdentifierPrefixesQueryEndToEnd extends TestCase
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

    /** @test */
    public function it_should_generate_prefixes_on_save(): void
    {
        $productFromDatabase = $this->createProduct('my_identifier_123');
        Assert::assertEquals($this->getPrefixes($productFromDatabase->getUuid(), $this->getIdentifierId()), [
            'my_identifier_' => '123',
            'my_identifier_1' => '23',
            'my_identifier_12' => '3',
        ]);
    }

    /** @test */
    public function it_should_update_prefixes_on_update(): void
    {
        $productFromDatabase = $this->createProduct('my_identifier_123');
        $this->setIdentifier($productFromDatabase, 'my_new_identifier_234');
        Assert::assertEquals($this->getPrefixes($productFromDatabase->getUuid(), $this->getIdentifierId()), [
            'my_new_identifier_' => '234',
            'my_new_identifier_2' => '34',
            'my_new_identifier_23' => '4',
        ]);
    }

    /** @test */
    public function it_should_remove_prefixes_on_delete(): void
    {
        $productFromDatabase = $this->createProduct('my_identifier_123');
        $this->deleteProduct($productFromDatabase);
        Assert::assertEquals($this->getPrefixes($productFromDatabase->getUuid(), $this->getIdentifierId()), []);
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

    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
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

    private function getProductRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product');
    }

    private function getPrefixes(UuidInterface $productUuid, $identifierId): array
    {
        $sql = <<<SQL
SELECT `prefix`, `number`
FROM pim_catalog_identifier_generator_prefixes
WHERE product_uuid=UUID_TO_BIN("%s")
AND attribute_id=%d
SQL;

        return $this->getConnection()->fetchAllKeyValue(\sprintf($sql, $productUuid->toString(), $identifierId));
    }

    private function getIdentifierId(): int
    {
        $sql = <<<SQL
SELECT `id`
FROM pim_catalog_attribute
WHERE attribute_type='pim_catalog_identifier'
SQL;

        return \intval($this->getConnection()->fetchOne($sql));
    }

    private function deleteProduct(ProductInterface $product): void
    {
        $this->getProductRemover()->remove($product);
    }
}
