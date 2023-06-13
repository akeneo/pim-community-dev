<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductIdentifiersOnProductUpdateIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_sets_and_updates_the_product_identifiers_table_on_product_create_and_update(): void
    {
        $productUuid = Uuid::uuid4();
        $this->upsertProduct($productUuid, [
            new SetIdentifierValue('sku', 'toto')
        ]);

        Assert::assertEqualsCanonicalizing(
            ['sku#toto'],
            $this->getIdentifierDataForProduct($productUuid)
        );

// @TODO: CPM-1066: Enable second part of the test once we can have multiple identifier attributes
//
//        $this->createIdentifierAttribute('ean');
//        $this->upsertProduct($productUuid, [
//            new SetIdentifierValue('ean', 'michel'),
//            new SetIdentifierValue('sku', ''),
//        ]);
//
//        Assert::assertEqualsCanonicalizing(
//            ['ean#michel'],
//            $this->getIdentifierDataForProduct($productUuid)
//        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function upsertProduct(UuidInterface $uuid, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createWithUuid(
            userId: $this->getUserId('admin'),
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function getIdentifierDataForProduct(UuidInterface $uuid): array
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $identifiersData = $connection->executeQuery(<<<SQL
SELECT identifiers from pim_catalog_product_identifiers
WHERE product_uuid = :uuid
SQL,
        ['uuid' => $uuid->getBytes()]
        )->fetchOne();

        return json_decode($identifiersData);
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    private function createIdentifierAttribute(string $attributeCode): AttributeInterface
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::IDENTIFIER,
            'useable_as_grid_filter' => true,
            'group' => 'other'
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }
}
