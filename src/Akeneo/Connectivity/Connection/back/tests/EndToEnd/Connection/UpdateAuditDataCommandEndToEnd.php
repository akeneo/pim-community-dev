<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateAuditDataCommandEndToEnd extends WebTestCase
{
    /** @var DbalConnection */
    private $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbalConnection = self::$container->get('database_connection');
    }

    public function test_it_updates_audit_data(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE);

        $this->setVersioningAuthor($connection->username());

        $command = $application->find('akeneo:connectivity-audit:update-data');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        Assert::assertEquals(242, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_CREATED));
        Assert::assertEquals(0, $this->getAuditCount($connection->code(), EventTypes::PRODUCT_UPDATED));
    }

    private function getAuditCount(string $connectionCode, string $eventType): int
    {
        $sqlQuery = <<<SQL
SELECT event_count
FROM akeneo_pim.akeneo_connectivity_connection_audit
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
UPDATE `akeneo_pim`.`pim_versioning_version`
SET `author` = :author
SQL;

        $stmt = $this->dbalConnection->prepare($sqlQuery);
        $stmt->execute([
            'author' => $author,
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
