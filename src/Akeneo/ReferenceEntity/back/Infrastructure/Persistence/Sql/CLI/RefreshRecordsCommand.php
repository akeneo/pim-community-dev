<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\CLI;

use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords\FindAllRecordIdentifiers;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords\RefreshRecord;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command refreshes all the records after to have a record linked, all records of a reference entity or attribute options linked deleted.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshRecordsCommand extends Command
{
    protected static $defaultName = self::REFRESH_RECORDS_COMMAND_NAME;

    public const REFRESH_RECORDS_COMMAND_NAME = 'akeneo:reference-entity:refresh-records';
    private const BULK_SIZE = 100;

    /** @var Connection */
    private $sqlConnection;

    /** @var FindAllRecordIdentifiers */
    private $findAllRecordIdentifiers;

    /** @var RefreshRecord */
    private $refreshRecord;
    
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        FindAllRecordIdentifiers $findAllRecordIdentifiers,
        RefreshRecord $refreshRecord,
        Connection $sqlConnection
    ) {
        parent::__construct(self::REFRESH_RECORDS_COMMAND_NAME);

        $this->sqlConnection = $sqlConnection;
        $this->findAllRecordIdentifiers = $findAllRecordIdentifiers;
        $this->refreshRecord = $refreshRecord;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Refresh all records referencing a deleted record or a deleted attribute option.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose') ?: false;

        $totalRecords = $this->getTotalRecords();
        if ($verbose) {
            $progressBar = new ProgressBar($output, $totalRecords);
            $progressBar->start();
        }
        $startedTime = new \DateTimeImmutable('now');

        $recordIdentifiers = $this->findAllRecordIdentifiers->fetch();
        $i = 0;
        foreach ($recordIdentifiers as $recordIdentifier) {
            $this->refreshRecord->refresh($recordIdentifier);
            if ($i % self::BULK_SIZE === 0) {
                if ($verbose) {
                    $progressBar->advance(self::BULK_SIZE);
                }
            }
            $i++;
        }
        if ($verbose) {
            $progressBar->finish();
        }
        $ruleRunDuration = $startedTime->diff(new \DateTimeImmutable('now'));
        $this->logger->notice(
            'reference-entity refresh-records',
            ['duration' => $ruleRunDuration->format('%s.%fs'), 'refresh_records' => $totalRecords]
        );
    }

    private function getTotalRecords(): int
    {
        $stmt = $this->sqlConnection->executeQuery('SELECT COUNT(*) FROM akeneo_reference_entity_record;');
        $result = $stmt->fetch(\PDO::FETCH_COLUMN);
        if (false === $result) {
            throw new \RuntimeException('An exception occurred while connecting the database');
        }

        return Type::getType('integer')->convertToPHPValue($result, $this->sqlConnection->getDatabasePlatform());
    }
}
