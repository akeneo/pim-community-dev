<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\CLI;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class RefreshRecordsCommand extends ContainerAwareCommand
{
    public const REFRESH_RECORDS_COMMAND_NAME = 'akeneo:reference-entity:refresh-records';
    private const BATCH_SIZE = 100;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var Client */
    private $recordClient;

    /** @var Connection */
    private $sqlConnection;

    /** @var ProgressBar */
    private $progressBar;

    /** @var int */
    private $totalRecords;

    /** @var int */
    private $nbRecordsRefreshed = 0;

    /** @var int */
    private $size = self::BATCH_SIZE;

    /** @var int */
    private $page = 0;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        Client $recordClient,
        Connection $sqlConnection
    ) {
        parent::__construct(self::REFRESH_RECORDS_COMMAND_NAME);

        $this->recordRepository = $recordRepository;
        $this->recordClient = $recordClient;
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::REFRESH_RECORDS_COMMAND_NAME)
            ->addOption(
                'all',
                true,
                InputOption::VALUE_NONE,
                'Refresh all existing records into Elasticsearch'
            )
            ->setDescription('Resets all records that have the record, the records of a reference entity or the attribute option deleted.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $isIndexAll = $input->getOption('all');
        if (!$isIndexAll) {
            $output->writeln('Please use the flag --all to refresh all records');
        }

        $this->totalRecords = $this->getTotalRecords();
        $this->progressBar = new ProgressBar($output, $this->totalRecords);
        $this->progressBar->start();

        while ($this->nbRecordsRefreshed < $this->totalRecords) {
            $recordIdentifiersResult = $this->fetchRecords($this->page, $this->size);
            $this->refreshRecords($recordIdentifiersResult);
            $this->progressBar->advance(100);

            if ($this->totalRecords - $this->nbRecordsRefreshed < self::BATCH_SIZE) {
                $this->size = $this->totalRecords - $this->nbRecordsRefreshed;
            }
            $this->page++;
        }

        $this->progressBar->finish();
    }

    private function getTotalRecords(): int
    {
        $stmt = $this->sqlConnection->executeQuery('SELECT COUNT(*) FROM akeneo_reference_entity_record;');
        $result = $stmt->fetch(\PDO::FETCH_COLUMN);
        if (false === $result) {
            throw new \RuntimeException('An exception occured while connecting the database');
        }

        return Type::getType('integer')->convertToPHPValue($result, $this->sqlConnection->getDatabasePlatform());
    }

    private function fetchRecords(int $page, int $size): IdentifiersForQueryResult
    {
        $query = [
            '_source' => '_id',
            'from'    => $size * $page,
            'size'    => $size,
        ];
        $matches = $this->recordClient->search('pimee_reference_entity_record', $query);
        $identifiers = array_map(function (array $hit) {
            return $hit['_id'];
        }, $matches['hits']['hits']);

        return new IdentifiersForQueryResult($identifiers, $matches['hits']['total']);
    }

    private function refreshRecords(IdentifiersForQueryResult $recordIdentifiersResult): void
    {
        foreach ($recordIdentifiersResult->identifiers as $recordIdentifier) {
            $record = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($recordIdentifier));
            $this->recordRepository->update($record);
            $this->nbRecordsRefreshed++;
            if ($this->nbRecordsRefreshed === $this->totalRecords) {
                break;
            }
        }
    }
}
