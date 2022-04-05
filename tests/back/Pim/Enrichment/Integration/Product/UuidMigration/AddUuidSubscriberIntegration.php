<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\UuidMigration;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddUuidSubscriberIntegration extends AbstractMigrateToUuidTestCase
{
    /** @test */
    public function it_adds_a_uuid_during_product_creation(): void
    {
        $this->connection = $this->get('database_connection');
        $adminUser = $this->createAdminUser();
        $this->createAttribute(['code' => 'a_text', 'type' => AttributeTypes::TEXT]);

        $this->assertProductCanBeCreatedAndHaveUuid($adminUser->getId());
    }

    private function assertProductCanBeCreatedAndHaveUuid(int $adminUserId): void
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(new UpsertProductCommand(
            userId: $adminUserId,
            productIdentifier: 'product_with_uuid',
            valueUserIntents: [new SetTextValue('a_text', null, null, 'test1')]
        ));
        $sql = "SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = 'product_with_uuid'";
        $uuid = $this->connection->executeQuery($sql)->fetchOne();
        Assert::assertNotNull($uuid);
        Assert::assertTrue(Uuid::isValid($uuid));

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
        $this->get('pim_enrich.product.message_bus')->dispatch(new UpsertProductCommand(
            userId: $adminUserId,
            productIdentifier: 'product_with_uuid',
            valueUserIntents: [new SetTextValue('a_text', null, null, 'test2')]
        ));
        $sql = "SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = 'product_with_uuid'";
        $uuidAfterUpdate = $this->connection->executeQuery($sql)->fetchOne();
        Assert::assertNotNull($uuidAfterUpdate);
        Assert::assertSame($uuid, $uuidAfterUpdate);
    }
}
