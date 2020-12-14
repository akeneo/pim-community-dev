<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryChannelExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFileExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFileStorer;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindFileDataByFileKey;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryGetJobExecutionStatus;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use AkeneoEnterprise\Test\Acceptance\EventDispatcher\EventDispatcherMock;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ImportRecordContext implements Context
{
    private const VALID_CSV_FILEPATH = 'src/Akeneo/ReferenceEntity/tests/back/Common/Resources/import_valid_records.csv';
    private const INVALID_CSV_FILEPATH = 'src/Akeneo/ReferenceEntity/tests/back/Common/Resources/import_invalid_records.csv';
    private const VALID_XLSX_FILEPATH = 'src/Akeneo/ReferenceEntity/tests/back/Common/Resources/import_valid_records.xlsx';
    private const VALID_ARCHIVE_WITH_CSV_AND_MEDIA_FILEPATH = 'src/Akeneo/ReferenceEntity/tests/back/Common/Resources/import_records_csv_with_media.zip';
    private const VALID_ARCHIVE_WITH_XLSX_AND_MEDIA_FILEPATH = 'src/Akeneo/ReferenceEntity/tests/back/Common/Resources/import_records_xlsx_with_media.zip';

    private ItemStep $importCsvRecordStep;
    private ItemStep $importXlsxRecordStep;
    private DefaultValuesProviderInterface $csvDefaultValuesProvider;
    private DefaultValuesProviderInterface $xlsxDefaultValuesProvider;
    private InMemoryChannelExists $channelExists;
    private InMemoryFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels;
    private InMemoryFindActivatedLocalesByIdentifiers $findActivatedLocalesByIdentifiers;
    private ExceptionContext $exceptionContext;
    private EventDispatcherMock $eventDispatcher;
    private InMemoryFileExists $fileExists;
    private InMemoryFindFileDataByFileKey $findFileDataByFileKey;
    private InMemoryGetJobExecutionStatus $getJobExecutionStatus;

    public function __construct(
        ItemStep $importCsvRecordStep,
        ItemStep $importXlsxRecordStep,
        DefaultValuesProviderInterface $csvDefaultValuesProvider,
        DefaultValuesProviderInterface $xlsxDefaultValuesProvider,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        InMemoryFindActivatedLocalesByIdentifiers $findActivatedLocalesByIdentifiers,
        ExceptionContext $exceptionContext,
        EventDispatcherMock $eventDispatcher,
        InMemoryFileExists $fileExists,
        InMemoryFindFileDataByFileKey $findFileDataByFileKey,
        InMemoryGetJobExecutionStatus $getJobExecutionStatus
    ) {
        $this->importCsvRecordStep = $importCsvRecordStep;
        $this->importXlsxRecordStep = $importXlsxRecordStep;
        $this->csvDefaultValuesProvider = $csvDefaultValuesProvider;
        $this->xlsxDefaultValuesProvider = $xlsxDefaultValuesProvider;
        $this->channelExists = $channelExists;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
        $this->findActivatedLocalesByIdentifiers = $findActivatedLocalesByIdentifiers;
        $this->exceptionContext = $exceptionContext;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileExists = $fileExists;
        $this->findFileDataByFileKey = $findFileDataByFileKey;
        $this->getJobExecutionStatus = $getJobExecutionStatus;
    }

    /**
     * @Given /^the \'([^\']*)\' channels? with \'([^\']*)\' locales?$/
     */
    public function theChannels(string $channelCodes, string $localeCodes): void
    {
        $localeCodes = explode(',', $localeCodes);
        foreach (explode(',', $channelCodes) as $channelCode) {
            $this->channelExists->save(ChannelIdentifier::fromCode($channelCode));

            $this->findActivatedLocalesPerChannels->save($channelCode, $localeCodes);
            foreach ($localeCodes as $localeCode) {
                $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode($localeCode));
            }
        }
    }

    /**
     * @When the user imports a valid CSV file
     */
    public function importValidCSVFile(): void
    {
        $this->launchImportCsvRecordStep(self::VALID_CSV_FILEPATH);
    }

    /**
     * @When the user imports an invalid CSV file
     */
    public function importInvalidCSVFile(): void
    {
        $this->launchImportCsvRecordStep(self::INVALID_CSV_FILEPATH);
    }

    /**
     * @When the user imports a valid XLSX file
     */
    public function importValidXLSXFile(): void
    {
        $this->launchImportXlsxRecordStep(self::VALID_XLSX_FILEPATH);
    }

    /**
     * @When the user imports a valid archive file with csv and media
     */
    public function importValidArchiveWithCsvFile(): void
    {
        $fileKey = InMemoryFileStorer::FILES_PATH . 'dog.jpg';
        $this->fileExists->save($fileKey);
        $this->findFileDataByFileKey->save([
            'filePath' => $fileKey,
            'originalFilename' => 'dog.jpg',
            'extension' => 'jpg',
        ]);

        $this->launchImportCsvRecordStep(self::VALID_ARCHIVE_WITH_CSV_AND_MEDIA_FILEPATH);
    }

    /**
     * @When the user imports a valid archive file with xlsx and media
     */
    public function importValidArchiveWithXlsxFile(): void
    {
        $fileKey = InMemoryFileStorer::FILES_PATH . 'dog.jpg';
        $this->fileExists->save($fileKey);
        $this->findFileDataByFileKey->save([
            'filePath' => $fileKey,
            'originalFilename' => 'dog.jpg',
            'extension' => 'jpg',
        ]);

        $this->launchImportXlsxRecordStep(self::VALID_ARCHIVE_WITH_XLSX_AND_MEDIA_FILEPATH);
    }

    /**
     * @Then there is no warning thrown
     */
    public function thereIsNoWarningThrown(): void
    {
        $invalidItemEvents = $this->eventDispatcher->getEventsByName('akeneo_batch.invalid_item');

        $errorReasons = array_map(
            fn (InvalidItemEvent $event): string => $event->getReason(),
            $invalidItemEvents
        );

        Assert::isEmpty($invalidItemEvents, sprintf(
            '%d warning%s thrown: %s',
            count($invalidItemEvents),
            count($invalidItemEvents) > 1 ? 's are' : ' is',
            implode(', ', $errorReasons)
        ));
    }

    /**
     * @Then /^a warning should be thrown with \'([^\']*)\' message$/
     */
    public function aWarningShouldBeThrownWithMessage(string $message): void
    {
        $errorReasons = [];
        $invalidItemEvents = $this->eventDispatcher->getEventsByName('akeneo_batch.invalid_item');
        foreach ($invalidItemEvents as $event) {
            Assert::isInstanceOf($event, InvalidItemEvent::class);

            $errorReasons[] = $event->getReason();
            if (strpos($event->getReason(), $message) !== false) {
                return;
            }
        }

        throw new \Exception(0 === count($errorReasons)
            ? 'No warning has been thrown'
            : sprintf('The message is not found. Got %s', implode(', ', $errorReasons))
        );
    }

    /**
     * @Then /^(\d+) warnings? should be thrown$/
     */
    public function warningShouldBeThrown(int $count): void
    {
        $invalidItemEvents = $this->eventDispatcher->getEventsByName('akeneo_batch.invalid_item');

        Assert::count($invalidItemEvents, $count);
    }

    private function launchImportCsvRecordStep(string $filePath): void
    {
        $jobExecution = new JobExecution();
        $reflectionClass = new \ReflectionClass(JobExecution::class);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($jobExecution, 1);
        $this->getJobExecutionStatus->setJobExecutionIdStatus(1, new BatchStatus(BatchStatus::STARTED));

        $params = $this->csvDefaultValuesProvider->getDefaultValues();
        $params['filePath'] = $filePath;
        $jobParameters = new JobParameters($params);
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('import_csv_record', $jobExecution);

        try {
            $this->importCsvRecordStep->doExecute($stepExecution);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    private function launchImportXlsxRecordStep(string $filePath): void
    {
        $jobExecution = new JobExecution();
        $reflectionClass = new \ReflectionClass(JobExecution::class);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($jobExecution, 1);
        $this->getJobExecutionStatus->setJobExecutionIdStatus(1, new BatchStatus(BatchStatus::STARTED));

        $params = $this->xlsxDefaultValuesProvider->getDefaultValues();
        $params['filePath'] = $filePath;
        $jobParameters = new JobParameters($params);
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('import_xlsx_record', $jobExecution);

        try {
            $this->importXlsxRecordStep->doExecute($stepExecution);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}
