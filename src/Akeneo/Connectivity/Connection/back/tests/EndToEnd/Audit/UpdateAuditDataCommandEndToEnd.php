<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\CommandTestCase;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection as DbalConnection;
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
    /** @var Command */
    private $command;

    /** @var DbalConnection */
    private $dbalConnection;

    /** @var string */
    private $productClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = $this->application->find('akeneo:connectivity-audit:update-data');
        $this->dbalConnection = self::$container->get('database_connection');
        $this->productClass = self::$container->getParameter('pim_catalog.entity.product.class');
    }

    public function test_it_updates_audit_data(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, true);

        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));

        $product1 = $this->createProduct('product1', ['enabled' => false]);
        $this->createProduct('product2', ['enabled' => false]);
        $product3 = $this->createProduct('product3', ['enabled' => false]);
        $this->setVersioningAuthor($connection->username());

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));

        $this->updateProduct($product1, ['enabled' => true]);
        $this->updateProduct($product1, ['enabled' => false]);
        $this->updateProduct($product3, ['enabled' => true]);
        $this->setVersioningAuthor($connection->username());

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));
    }

    public function test_updates_audit_data_only_for_auditable_connection()
    {
        $erpConnection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $otherConnection = $this->createConnection('another_source', 'Another source', FlowType::DATA_SOURCE, false);

        Assert::assertEquals(0, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_UPDATED));

        $product1 = $this->createProduct('product1', ['enabled' => false]);
        $product2 = $this->createProduct('product2', ['enabled' => false]);
        $this->setVersioningAuthor($erpConnection->username(), $product1);
        $this->setVersioningAuthor($otherConnection->username(), $product2);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(1, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_UPDATED));

        $this->updateProduct($product1, ['enabled' => true]);
        $this->updateProduct($product2, ['enabled' => true]);
        $this->setVersioningAuthor($erpConnection->username(), $product1);
        $this->setVersioningAuthor($otherConnection->username(), $product2);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(1, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(1, $this->getAuditCount($erpConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($otherConnection->code(), EventTypes::PRODUCT_UPDATED));
    }

    public function test_updates_audit_data_only_for_data_source_connection()
    {
        $sourceConnection = $this->createConnection('source', 'Source', FlowType::DATA_SOURCE, true);
        $destinationConnection = $this->createConnection('destination', 'Destination', FlowType::DATA_DESTINATION, true);

        Assert::assertEquals(0, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_UPDATED));

        $product1 = $this->createProduct('product1', ['enabled' => false]);
        $product2 = $this->createProduct('product2', ['enabled' => false]);
        $this->setVersioningAuthor($sourceConnection->username(), $product1);
        $this->setVersioningAuthor($destinationConnection->username(), $product2);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(1, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($sourceConnection->code(), EventTypes::PRODUCT_UPDATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($destinationConnection->code(), EventTypes::PRODUCT_UPDATED));

        $this->updateProduct($product1, ['enabled' => true]);
        $this->updateProduct($product2, ['enabled' => true]);
        $this->setVersioningAuthor($sourceConnection->username(), $product1);
        $this->setVersioningAuthor($destinationConnection->username(), $product2);

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

        return (int) $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchColumn();
    }

    private function setVersioningAuthor(string $author, ?ProductInterface $product = null): void
    {
        $sqlQuery = <<<SQL
UPDATE pim_versioning_version
SET author = :author
WHERE resource_name = :resource_name
SQL;
        $parameters = [
            'author' => $author,
            'resource_name' => $this->productClass,
        ];
        if (null !== $product) {
            $sqlQuery .= <<<SQL
 AND resource_id = :product_id
SQL;
            $parameters['product_id'] = $product->getId();
        }

        $stmt = $this->dbalConnection->prepare($sqlQuery);
        $stmt->execute($parameters);
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
