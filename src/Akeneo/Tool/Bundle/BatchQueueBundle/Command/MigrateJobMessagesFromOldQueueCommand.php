<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class MigrateJobMessagesFromOldQueueCommand extends Command
{
    private const BATCH = 1000;
    private const MIGRATION_CONSUMER = 'migration';

    protected static $defaultName = 'akeneo:batch:migrate-job-messages-from-old-queue';

    private JobExecutionMessageFactory $jobExecutionMessageFactory;
    private JobExecutionQueueInterface $jobExecutionQueue;
    private EntityManagerClearerInterface $entityManagerClearer;
    private Connection $connection;

    public function __construct(
        JobExecutionMessageFactory $jobExecutionMessageFactory,
        JobExecutionQueueInterface $jobExecutionQueue,
        EntityManagerClearerInterface $entityManagerClearer,
        Connection $connection
    ) {
        parent::__construct();

        $this->jobExecutionMessageFactory = $jobExecutionMessageFactory;
        $this->jobExecutionQueue = $jobExecutionQueue;
        $this->entityManagerClearer = $entityManagerClearer;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Migrate job messages from old queue to the new queue using Symfony Messenger')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Display the number of job messages to migrate without executing migration.'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Number of the job messages to migrate. Default is all.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit') ?? 0;
        if (!$this->jobQueueTableExists()) {
            $output->writeln('The old job queue table is already dropped.');
            return 0;
        }

        $numberOfJobMessagesToMigrate = $this->getNumberOfJobMessagesToMigrate();
        if (0 === $numberOfJobMessagesToMigrate) {
            $output->writeln('<info>No job message found in the old queue.</info>');
            return 0;
        }

        $output->writeln(sprintf('<info>Found %d job message(s) to migrate.</info>', $numberOfJobMessagesToMigrate));
        if ($input->getOption('dry-run')) {
            $output->writeln('<info>Dry run mode activated, process is stopped.</info>');
            return 0;
        }

        $helper = $this->getHelper('question');
        $continueQuestion = new ConfirmationQuestion(
            sprintf('Migrate %d job messages? (Y/n)', $limit > 0 ? $limit : $numberOfJobMessagesToMigrate),
            true
        );
        if (!$helper->ask($input, $output, $continueQuestion)) {
            return 0;
        }

        $progressBar = new ProgressBar($output, $limit > 0 ? $limit : $numberOfJobMessagesToMigrate);
        $progressBar->start();
        $jobExecutionMessageBatches = $this->getNotConsumedJobExecutionMessages();

        $migratedMessagecount = 0;
        foreach ($jobExecutionMessageBatches as $jobExecutionMessageBatch) {
            foreach ($jobExecutionMessageBatch as $oldId => $jobExecutionMessage) {
                $this->jobExecutionQueue->publish($jobExecutionMessage);
                $this->markJobMessageAsConsumed($oldId);
                $progressBar->advance();

                $migratedMessagecount++;
                if ($limit > 0 && $migratedMessagecount >= $limit) {
                    break 2;
                }
            }
        }

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf('<info>Terminated, %d job message(s) are migrated.</info>', $migratedMessagecount));

        return 0;
    }

    private function getNumberOfJobMessagesToMigrate(): int
    {
        $query = 'SELECT count(id) FROM akeneo_batch_job_execution_queue WHERE consumer IS NULL';

        return (int) $this->connection->executeQuery($query)->fetchColumn();
    }

    private function getNotConsumedJobExecutionMessages(): \Iterator
    {
        $query = <<<SQL
        SELECT id, job_execution_id, create_time AS created_time, updated_time, options, consumer
        FROM akeneo_batch_job_execution_queue
        WHERE consumer IS NULL AND id > :id
        ORDER BY id
        LIMIT :batch
        SQL;

        $platform = $this->connection->getDatabasePlatform();

        $lastId = 0;
        while (true) {
            $rows = $this->connection->executeQuery(
                $query,
                ['batch' => self::BATCH, 'id' => $lastId],
                ['batch' => Types::INTEGER, 'id' => Types::INTEGER]
            )->fetchAll();

            if (0 === count($rows)) {
                break;
            }

            $jobMessages = [];
            foreach ($rows as $row) {
                $lastId = Type::getType(Types::INTEGER)->convertToPhpValue($row['id'], $platform);
                $row['old_id'] = $lastId;
                $row['id'] = Uuid::uuid4();
                $row['job_execution_id'] = Type::getType(Types::INTEGER)->convertToPhpValue($row['job_execution_id'], $platform);
                $row['options'] = Type::getType(Types::JSON)->convertToPhpValue($row['options'], $platform);
                $row['created_time'] = Type::getType(Types::STRING)->convertToPhpValue($row['created_time'], $platform);
                $row['updated_time'] = Type::getType(Types::STRING)->convertToPhpValue($row['updated_time'], $platform);
                $row['consumer'] = Type::getType(Types::STRING)->convertToPhpValue($row['consumer'], $platform);

                $jobMessages[$row['old_id']] = $this->jobExecutionMessageFactory->buildFromNormalized($row);
            }

            yield $jobMessages;

            $this->entityManagerClearer->clear();
        }
    }

    private function jobQueueTableExists(): bool
    {
        return 1 <= $this
                ->connection
                ->executeQuery("SHOW TABLES LIKE 'akeneo_batch_job_execution_queue';")
                ->rowCount();
    }

    private function markJobMessageAsConsumed(int $id): void
    {
        $query = 'UPDATE akeneo_batch_job_execution_queue SET consumer = :consumer WHERE id = :id';

        $this->connection->executeQuery($query, [
            'consumer' => self::MIGRATION_CONSUMER,
            'id' => $id,
        ]);
    }
}
