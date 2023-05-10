<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit\Command;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\CommandTestCase;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateAuditDataCommandEndToEnd extends CommandTestCase
{
    private Command $command;
    private DbalConnection $dbalConnection;
    private string $productClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = $this->application->find('akeneo:connectivity-audit:update-data');
        $this->dbalConnection = self::getContainer()->get('database_connection');
        $this->productClass = self::getContainer()->getParameter('pim_catalog.entity.product.class');
    }

    public function test_it_updates_audit_data(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, true);

        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));

        $product1 = $this->createProduct('product1', ['enabled' => false]);
        $this->createProduct('product2', ['enabled' => false]);
        $product3 = $this->createProduct('product3', ['enabled' => false]);
        $this->setVersioningAuthorAndDate($connection->username());

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));

        $this->updateProduct($product1, ['enabled' => true]);
        $this->updateProduct($product1, ['enabled' => false]);
        $this->updateProduct($product3, ['enabled' => true]);
        $this->setVersioningAuthorAndDate($connection->username());

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));
    }

    public function test_updates_audit_data_only_for_auditable_connection(): void
    {
        $erpConnection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $otherConnection = $this->createConnection('another_source', 'Another source', FlowType::DATA_SOURCE, false);

        Assert::assertEquals(0, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_UPDATED));

        $product1 = $this->createProduct('product1', ['enabled' => false]);
        $product2 = $this->createProduct('product2', ['enabled' => false]);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $product1);
        $this->setVersioningAuthorAndDate($otherConnection->username(), $product2);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(1, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_UPDATED));

        $this->updateProduct($product1, ['enabled' => true]);
        $this->updateProduct($product2, ['enabled' => true]);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $product1);
        $this->setVersioningAuthorAndDate($otherConnection->username(), $product2);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(1, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(1, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_UPDATED));
    }

    public function test_updates_audit_data_only_for_data_source_connection(): void
    {
        $sourceConnection = $this->createConnection('source', 'Source', FlowType::DATA_SOURCE, true);
        $destinationConnection = $this->createConnection('destination', 'Destination', FlowType::DATA_DESTINATION, true);

        Assert::assertEquals(0, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_UPDATED));

        $product1 = $this->createProduct('product1', ['enabled' => false]);
        $product2 = $this->createProduct('product2', ['enabled' => false]);
        $this->setVersioningAuthorAndDate($sourceConnection->username(), $product1);
        $this->setVersioningAuthorAndDate($destinationConnection->username(), $product2);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(1, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_UPDATED));

        $this->updateProduct($product1, ['enabled' => true]);
        $this->updateProduct($product2, ['enabled' => true]);
        $this->setVersioningAuthorAndDate($sourceConnection->username(), $product1);
        $this->setVersioningAuthorAndDate($destinationConnection->username(), $product2);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(1, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(1, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_UPDATED));
    }

    private function getAuditCount(string $connectionCode, string $eventType): int
    {
        $sqlQuery = <<<SQL
SELECT event_count
FROM akeneo_connectivity_connection_audit_product
WHERE connection_code = :connection_code
AND event_type = :event_type
SQL;

        $sqlParams = [
            'connection_code' => $connectionCode,
            'event_type' => $eventType,
        ];

        return (int) $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchOne();
    }

    private function setVersioningAuthorAndDate(
        string $author,
        ?ProductInterface $product = null,
        ?\DateTimeImmutable $dateTime = null
    ): void {
        if (null === $dateTime) {
            $dateTime = new \DateTimeImmutable('1 hour ago', new \DateTimeZone('UTC'));
        }

        $sqlQuery = <<<SQL
UPDATE pim_versioning_version
SET author = :author, logged_at = :logged_at
WHERE resource_name = :resource_name
SQL;
        $parameters = [
            'author' => $author,
            'resource_name' => $this->productClass,
            'logged_at' => $dateTime,
        ];

        if (null !== $product) {
            $sqlQuery .= <<<SQL
 AND resource_uuid = :product_uuid
SQL;
            $parameters['product_uuid'] = $product->getUuid()->getBytes();
        }

        $this->dbalConnection->executeQuery(
            $sqlQuery,
            $parameters,
            [
                'logged_at' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }

    private function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->updateProduct($product, $data);

        return $product;
    }

    private function updateProduct(ProductInterface $product, array $data = []): ProductInterface
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
