<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * It migrates non consumed jobs from the akeneo_batch_execution_queue table to the messenger transport.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210413070335_migrate_jobs_to_messenger extends AbstractMigration implements ContainerAwareInterface
{
    private const BATCH = 100;

    private ?ContainerInterface $container;
    private JobExecutionMessageFactory $jobExecutionMessageFactory;
    private JobExecutionQueueInterface $jobExecutionQueue;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        if (!$this->jobQueueTableExists()) {
            $this->disableMigrationWarning();
            $this->write('The job queue table is already dropped.');

            return;
        }

        $this->jobExecutionMessageFactory = $this->container->get('akeneo_batch_queue.factory.job_execution_message');
        $this->jobExecutionQueue = $this->container->get('akeneo_batch_queue.queue.job_execution_queue');

        $jobExecutionMessages = $this->getNotConsumedJobExecutionMessages();
        foreach ($jobExecutionMessages as $jobExecutionMessage) {
            $this->jobExecutionQueue->publish($jobExecutionMessage);
        }

        // TODO: decide if we drop the table now or in a next PR (if no set a consumer on the migrated jobs)
        $this->addSql('DROP TABLE akeneo_batch_job_execution_queue');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getNotConsumedJobExecutionMessages(): \Iterator
    {
        $sql = <<<SQL
        SELECT id, job_execution_id, create_time AS created_time, updated_time, options, consumer
        FROM akeneo_batch_job_execution_queue
        WHERE consumer IS NULL
        ORDER BY create_time, id
        LIMIT :batch {sqlOffset}
        SQL;

        $platform = $this->connection->getDatabasePlatform();

        $offset = 0;
        while (true) {
            $query = str_replace(
                '{sqlOffset}',
                (0 === $offset) ? '' : sprintf('OFFSET %d', $offset),
                $sql
            );
            $offset += self::BATCH;

            $rows = $this->connection->executeQuery(
                $query,
                ['batch' => self::BATCH],
                ['batch' => Types::INTEGER]
            )->fetchAll();

            if (0 === count($rows)) {
                break;
            }

            foreach ($rows as $row) {
                $row['old_id'] = Type::getType(Types::INTEGER)->convertToPhpValue($row['id'], $platform);
                $row['id'] = Uuid::uuid4();
                $row['job_execution_id'] = Type::getType(Types::INTEGER)->convertToPhpValue($row['job_execution_id'], $platform);
                $row['options'] = Type::getType(Types::JSON)->convertToPhpValue($row['options'], $platform);
                $row['created_time'] = Type::getType(Types::STRING)->convertToPhpValue($row['created_time'], $platform);
                $row['updated_time'] = Type::getType(Types::STRING)->convertToPhpValue($row['updated_time'], $platform);
                $row['consumer'] = Type::getType(Types::STRING)->convertToPhpValue($row['consumer'], $platform);

                yield $this->jobExecutionMessageFactory->buildFromNormalized($row);
            }
        }
    }

    private function jobQueueTableExists(): bool
    {
        return 1 <= $this->connection->executeQuery("SHOW TABLES LIKE 'akeneo_batch_job_execution_queue';")->rowCount();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
