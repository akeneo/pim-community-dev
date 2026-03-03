<?php
declare(strict_types=1);

namespace AkeneoTest\Tool\Integration\Batch\Persistence\Sql;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlGetJobInstanceCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlGetJobInstanceCodeIntegration extends TestCase
{
    private Connection $connection;
    private SqlGetJobInstanceCode $sqlGetJobInstanceCode;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->sqlGetJobInstanceCode = $this->get('akeneo_batch.query.get_job_instance_code');
        $this->loadFixtures();
    }

    public function testFromJobExecutionId(): void
    {
        self::assertSame('test1', $this->sqlGetJobInstanceCode->fromJobExecutionId(1));
        self::assertSame('test1', $this->sqlGetJobInstanceCode->fromJobExecutionId(2));
        self::assertSame('test2', $this->sqlGetJobInstanceCode->fromJobExecutionId(3));
        self::assertNull($this->sqlGetJobInstanceCode->fromJobExecutionId(4));
    }

    private function loadFixtures()
    {
        $this->connection->executeQuery(<<<SQL
        INSERT INTO akeneo_batch_job_instance (id, code, job_name, status, connector, raw_parameters, type) VALUES
        (1, 'test1', '', 0, '', '', ''),
        (2, 'test2', '', 0, '', '', '');
        SQL);

        $this->connection->executeQuery(<<<SQL
        INSERT INTO akeneo_batch_job_execution (id, job_instance_id, create_time, status, raw_parameters) VALUES
        (1, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'),
        (2, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'),
        (3, 2, DATE_SUB(NOW(), INTERVAL 3 day), 1, '{}');
        SQL);
    }
}
