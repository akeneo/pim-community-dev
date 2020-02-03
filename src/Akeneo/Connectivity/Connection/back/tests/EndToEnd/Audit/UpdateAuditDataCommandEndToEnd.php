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

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = $this->application->find('akeneo:connectivity-audit:update-data');
        $this->dbalConnection = self::$container->get('database_connection');
    }

    public function test_it_updates_audit_data(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE);

        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));

        $product1 = $this->createProduct('product1', ['enabled' => false]);
        $product2 = $this->createProduct('product2', ['enabled' => false]);
        $product3 = $this->createProduct('product3', ['enabled' => false]);
        $this->setVersioningAuthor($connection->username());

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));

        $this->updateProduct($product1, ['enabled' => true]);
        $this->updateProduct($product3, ['enabled' => true]);
        $this->setVersioningAuthor($connection->username());

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        Assert::assertEquals(3, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(2, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));
    }

    private function getAuditCount(string $connectionCode, string $eventType): int
    {
        $sqlQuery = <<<SQL
SELECT event_count
FROM akeneo_connectivity_connection_audit
WHERE connection_code = :connection_code
AND event_type = :event_type
SQL;

        $sqlParams = [
            'connection_code' => $connectionCode,
            'event_type' => $eventType,
        ];

        return (int) $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchColumn();
    }

    private function setVersioningAuthor(string $author): void
    {
        $sqlQuery = <<<SQL
UPDATE pim_versioning_version
SET author = :author
SQL;

        $stmt = $this->dbalConnection->prepare($sqlQuery);
        $stmt->execute([
            'author' => $author,
        ]);
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
