<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Job;

use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords\FindAllRecordIdentifiers;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords\RefreshRecord;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

/**
 * This command refreshes all the records after to have a record linked,
 * all records of a reference entity or attribute options linked deleted.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshRecordsTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'reference_entity_refresh_records';
    public function __construct(
        private LoggerInterface $logger,
        private FindAllRecordIdentifiers $findAllRecordIdentifiers,
        private RefreshRecord $refreshRecord,
        private CountRecordsInterface $countRecords,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
    }

    public function execute(): void
    {
        $totalRecords = $this->countRecords->all();

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
            }
        }
        $ruleRunDuration = $startedTime->diff(new \DateTimeImmutable('now'));
        $this->logger->notice(
            'reference-entity refresh-records',
            ['duration' => $ruleRunDuration->format('%s.%fs'), 'refresh_records' => $totalRecords]
        );
    }
}
