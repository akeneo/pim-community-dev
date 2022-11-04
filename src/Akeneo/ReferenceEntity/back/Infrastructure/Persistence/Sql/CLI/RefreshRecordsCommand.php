<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\CLI;

use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords\FindAllRecordIdentifiers;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords\RefreshRecord;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command refreshes all the records after to have a record linked,
 * all records of a reference entity or attribute options linked deleted.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshRecordsCommand extends Command
{
    protected static $defaultName = self::REFRESH_RECORDS_COMMAND_NAME;

    public const REFRESH_RECORDS_COMMAND_NAME = 'akeneo:reference-entity:refresh-records';
    private const BULK_SIZE = 100;

    public function __construct(
        private LoggerInterface $logger,
        private FindAllRecordIdentifiers $findAllRecordIdentifiers,
        private RefreshRecord $refreshRecord,
        private CountRecordsInterface $countRecords,
    ) {
        parent::__construct(self::REFRESH_RECORDS_COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Refresh all records referencing a deleted record or a deleted attribute option.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $verbose = $input->getOption('verbose') ?: false;

        $totalRecords = $this->countRecords->all();
        $progressBar = new ProgressBar($output, $totalRecords);
        if ($verbose) {
            $progressBar->start();
        }

        $startedTime = new \DateTimeImmutable('now');

        $recordIdentifiers = $this->findAllRecordIdentifiers->fetch();
        $i = 0;
        foreach ($recordIdentifiers as $recordIdentifier) {
            try {
                $this->refreshRecord->refresh($recordIdentifier);
            } catch (RecordNotFoundException) {
                continue;
            } finally {
                $i++;
                if ($i % self::BULK_SIZE === 0 && $verbose) {
                    $progressBar->advance(self::BULK_SIZE);
                }
            }
        }
        if ($verbose) {
            $progressBar->finish();
        }
        $ruleRunDuration = $startedTime->diff(new \DateTimeImmutable('now'));
        $this->logger->notice(
            'reference-entity refresh-records',
            ['duration' => $ruleRunDuration->format('%s.%fs'), 'refresh_records' => $totalRecords]
        );

        return 0;
    }
}
