<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\CLI;

use Akeneo\ReferenceEntity\back\Infrastructure\Persistence\Sql\Record\RefreshRecords\RefreshAllRecords;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
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

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var Connection */
    private $sqlConnection;

    /** @var ProgressBar */
    private $progressBar;

    /** @var int */
    private $totalRecords;

    /** @var RefreshAllRecords */
    private $refreshRecords;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        RefreshAllRecords $refreshRecords,
        Connection $sqlConnection
    ) {
        parent::__construct(self::REFRESH_RECORDS_COMMAND_NAME);

        $this->recordRepository = $recordRepository;
        $this->sqlConnection = $sqlConnection;
        $this->refreshRecords = $refreshRecords;
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

        $this->refreshRecords->execute();

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
}
