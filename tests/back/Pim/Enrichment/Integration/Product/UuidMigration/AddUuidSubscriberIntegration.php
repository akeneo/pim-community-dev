<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\UuidMigration;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddUuidSubscriberIntegration extends TestCase
{
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_adds_a_uuid_during_product_creation(): void
    {
        $this->connection = $this->get('database_connection');
        $adminUser = $this->createAdminUser();
        $this->createAttribute(['code' => 'a_text', 'type' => AttributeTypes::TEXT]);

        $this->assertProductCanBeCreatedBeforeMigration($adminUser->getId());

        $this->launchMigrationCommand();

        $this->assertProductCanBeCreatedAfterMigrationAndHaveUuid($adminUser->getId());
    }

    private function assertProductCanBeCreatedBeforeMigration(int $adminUserId): void
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(new UpsertProductCommand(
            userId: $adminUserId,
            productIdentifier: 'product_without_uuid'
        ));
        Assert::assertNotNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_uuid'));
    }

    private function assertProductCanBeCreatedAfterMigrationAndHaveUuid(int $adminUserId): void
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(new UpsertProductCommand(
            userId: $adminUserId,
            productIdentifier: 'product_with_uuid',
            valuesUserIntent: [new SetTextValue('a_text', null, null, 'test1')]
        ));
        $sql = "SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = 'product_with_uuid'";
        $uuid = $this->connection->executeQuery($sql)->fetchOne();
        Assert::assertNotNull($uuid);
        Assert::assertTrue(Uuid::isValid($uuid));

        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product
        $this->get('pim_enrich.product.message_bus')->dispatch(new UpsertProductCommand(
            userId: $adminUserId,
            productIdentifier: 'product_with_uuid',
            valuesUserIntent: [new SetTextValue('a_text', null, null, 'test2')]
        ));
        $sql = "SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = 'product_with_uuid'";
        $uuidAfterUpdate = $this->connection->executeQuery($sql)->fetchOne();
        Assert::assertNotNull($uuidAfterUpdate);
        Assert::assertSame($uuid, $uuidAfterUpdate);
    }

    private function launchMigrationCommand(): void
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'pim:product:migrate-to-uuid',
            '-v' => true,
        ]);
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Command failed: %s.', $output->fetch()));
        }
    }

    private function createAttribute(array $data)
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        $this->assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
