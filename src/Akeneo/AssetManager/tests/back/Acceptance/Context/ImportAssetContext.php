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

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFileStorer;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAttributesDetails;
use Akeneo\AssetManager\Common\Fake\InMemoryFindFileDataByFileKey;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryGetJobExecutionStatus;
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

final class ImportAssetContext implements Context
{
    private const VALID_CSV_FILEPATH = 'src/Akeneo/AssetManager/tests/back/Common/Resources/import_valid_assets.csv';
    private const INVALID_CSV_FILEPATH = 'src/Akeneo/AssetManager/tests/back/Common/Resources/import_invalid_assets.csv';
    private const VALID_XLSX_FILEPATH = 'src/Akeneo/AssetManager/tests/back/Common/Resources/import_valid_assets.xlsx';
    private const VALID_ARCHIVE_WITH_CSV_AND_MEDIA_FILEPATH = 'src/Akeneo/AssetManager/tests/back/Common/Resources/import_valid_assets_csv.zip';
    private const VALID_ARCHIVE_WITH_XLSX_AND_MEDIA_FILEPATH = 'src/Akeneo/AssetManager/tests/back/Common/Resources/import_valid_assets_xlsx.zip';

    private ItemStep $importCsvAssetStep;
    private ItemStep $importXlsxAssetStep;
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
    private InMemoryFindAttributesDetails $findAttributeDetails;

    public function __construct(
        ItemStep $importCsvAssetStep,
        ItemStep $importXlsxAssetStep,
        DefaultValuesProviderInterface $csvDefaultValuesProvider,
        DefaultValuesProviderInterface $xlsxDefaultValuesProvider,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        InMemoryFindActivatedLocalesByIdentifiers $findActivatedLocalesByIdentifiers,
        ExceptionContext $exceptionContext,
        EventDispatcherMock $eventDispatcher,
        InMemoryFileExists $fileExists,
        InMemoryFindFileDataByFileKey $findFileDataByFileKey,
        InMemoryGetJobExecutionStatus $getJobExecutionStatus,
        InMemoryFindAttributesDetails $findAttributeDetails
    ) {
        $this->importCsvAssetStep = $importCsvAssetStep;
        $this->importXlsxAssetStep = $importXlsxAssetStep;
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
        $this->findAttributeDetails = $findAttributeDetails;
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
        $this->saveDefaultAttributeDetails();
        $this->launchImportCsvAssetStep(self::VALID_CSV_FILEPATH);
    }

    /**
     * @When the user imports an invalid CSV file
     */
    public function importInvalidCSVFile(): void
    {
        $this->saveDefaultAttributeDetails();
        $this->launchImportCsvAssetStep(self::INVALID_CSV_FILEPATH);
    }

    /**
     * @When the user imports a valid XLSX file
     */
    public function importValidXLSXFile(): void
    {
        $this->saveDefaultAttributeDetails();
        $this->launchImportXlsxAssetStep(self::VALID_XLSX_FILEPATH);
    }

    /**
     * @When the user imports a valid archive file with csv and media
     */
    public function importValidArchiveWithCsvFile(): void
    {
        $this->saveDefaultAttributeDetails();
        foreach (['jambon.jpg', 'saucisson.jpg', 'rillettes-de-lapin.jpg'] as $fileName) {
            $fileKey = InMemoryFileStorer::FILES_PATH . $fileName;
            $this->fileExists->save($fileKey);
            $this->findFileDataByFileKey->save([
                'filePath' => $fileKey,
                'originalFilename' => $fileName,
                'extension' => 'jpg',
            ]);
        }

        $this->launchImportCsvAssetStep(self::VALID_ARCHIVE_WITH_CSV_AND_MEDIA_FILEPATH);
    }

    /**
     * @When the user imports a valid archive file with xlsx and media
     */
    public function importValidArchiveWithXlsxFile(): void
    {
        $this->saveDefaultAttributeDetails();
        foreach (['jambon.jpg', 'saucisson.jpg', 'rillettes-de-lapin.jpg'] as $fileName) {
            $fileKey = InMemoryFileStorer::FILES_PATH . $fileName;
            $this->fileExists->save($fileKey);
            $this->findFileDataByFileKey->save([
                'filePath' => $fileKey,
                'originalFilename' => $fileName,
                'extension' => 'jpg',
            ]);
        }

        $this->launchImportXlsxAssetStep(self::VALID_ARCHIVE_WITH_XLSX_AND_MEDIA_FILEPATH);
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

        Assert::count($invalidItemEvents, $count, sprintf(
            "%d warnings should be thrown, got %d:\n%s",
            $count,
            count($invalidItemEvents),
            join("\n", array_map(fn ($x) => $x->getReason(), $invalidItemEvents))
        ));
    }

    private function launchImportCsvAssetStep(string $filePath): void
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
        $stepExecution = new StepExecution('import_csv_asset', $jobExecution);

        try {
            $this->importCsvAssetStep->doExecute($stepExecution);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    private function launchImportXlsxAssetStep(string $filePath): void
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
        $stepExecution = new StepExecution('import_xlsx_asset', $jobExecution);

        try {
            $this->importXlsxAssetStep->doExecute($stepExecution);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    private function saveDefaultAttributeDetails()
    {
        $name = new AttributeDetails();
        $name->identifier = 'name';
        $name->assetFamilyIdentifier = 'designer';
        $name->code = 'name';
        $name->isRequired = true;
        $name->order = 0;
        $name->valuePerChannel = false;
        $name->valuePerLocale = false;
        $name->type = 'text';
        $name->labels = [];
        $name->isReadOnly = false;
        $this->findAttributeDetails->save($name);

        $media = new AttributeDetails();
        $media->identifier = 'media';
        $media->assetFamilyIdentifier = 'designer';
        $media->code = 'media';
        $media->isRequired = true;
        $media->order = 1;
        $media->valuePerChannel = false;
        $media->valuePerLocale = false;
        $media->type = 'media_file';
        $media->labels = [];
        $media->isReadOnly = false;
        $this->findAttributeDetails->save($media);
    }
}
