<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Job;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindRecordsUsedAsProductVariantAxisInterface;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsHandler;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordCursor;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteRecordsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(private DeleteRecordsHandler $deleteRecordsHandler, private RecordQueryBuilderInterface $recordQueryBuilder, private Client $recordClient, private JobRepositoryInterface $jobRepository, private RecordIndexerInterface $recordIndexer, private JobStopper $jobStopper, private ValidatorInterface $validator, private FindRecordsUsedAsProductVariantAxisInterface $findRecordsUsedAsProductVariantAxis, private int $batchSize)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return true;
    }

    public function execute(): void
    {
        $referenceEntityIdentifier = $this->stepExecution->getJobParameters()->get('reference_entity_identifier');
        $normalizedQuery = $this->stepExecution->getJobParameters()->get('query');
        $channel = ChannelReference::createFromNormalized($normalizedQuery['channel']);
        $locale = LocaleReference::createFromNormalized($normalizedQuery['locale']);
        $filters = $normalizedQuery['filters'];

        $recordQuery = RecordQuery::createWithSearchAfter(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $channel,
            $locale,
            $this->batchSize,
            null,
            $filters
        );

        $cursor = new RecordCursor($this->recordQueryBuilder, $this->recordClient, $recordQuery);
        $this->stepExecution->setTotalItems($cursor->count());

        $recordCodesToDelete = [];
        foreach ($cursor as $recordCode) {
            $recordCodesToDelete[] = $recordCode;

            if ($this->batchSize === count($recordCodesToDelete)) {
                if ($this->jobStopper->isStopping($this->stepExecution)) {
                    $this->jobStopper->stop($this->stepExecution);

                    break;
                }

                $this->deleteRecords($referenceEntityIdentifier, $recordCodesToDelete);

                $recordCodesToDelete = [];
            }
        }

        if ($this->jobStopper->isStopping($this->stepExecution)) {
            $this->jobStopper->stop($this->stepExecution);
            $this->recordIndexer->refresh();

            return;
        }

        if ([] !== $recordCodesToDelete) {
            $this->deleteRecords($referenceEntityIdentifier, $recordCodesToDelete);
        }

        $this->recordIndexer->refresh();
    }

    private function deleteRecords(string $referenceEntityIdentifier, array $recordCodesToDelete)
    {
        try {
            $recordCodesToDelete = $this->getValidRecordCodesToDelete($referenceEntityIdentifier, $recordCodesToDelete);

            $deleteRecordsCommand = new DeleteRecordsCommand($referenceEntityIdentifier, $recordCodesToDelete);
            $violations = $this->validator->validate($deleteRecordsCommand);

            if (0 < $violations->count()) {
                throw new \LogicException($this->buildErrorMessage($violations));
            }

            ($this->deleteRecordsHandler)($deleteRecordsCommand);
            $this->stepExecution->incrementSummaryInfo('records', count($recordCodesToDelete));
            $this->stepExecution->incrementProcessedItems(count($recordCodesToDelete));
            $this->jobRepository->updateStepExecution($this->stepExecution);
        } catch (\Exception $exception) {
            $this->stepExecution->addWarning(
                'akeneo_referenceentity.jobs.reference_entity_mass_delete.error',
                [
                    '{{ records }}' => (string) implode(', ', $recordCodesToDelete),
                    '{{ error }}' => $exception->getMessage(),
                ],
                new DataInvalidItem([
                    'record_codes' => (string) implode(', ', $recordCodesToDelete),
                    'error' => $exception->getMessage(),
                ]),
            );
        }
    }

    private function getValidRecordCodesToDelete(
        string $referenceEntityIdentifier,
        array $recordCodes
    ): array {
        $recordCodesUsedAsAxis = $this->findRecordsUsedAsProductVariantAxis->getUsedCodes(
            $recordCodes,
            $referenceEntityIdentifier
        );

        if (!empty($recordCodesUsedAsAxis)) {
            $this->stepExecution->addWarning(
                'akeneo_referenceentity.jobs.reference_entity_mass_delete.used_as_product_variant_axis',
                [],
                new DataInvalidItem(['record_codes' => (string) implode(', ', $recordCodesUsedAsAxis)]),
            );
        }

        return array_values(array_diff($recordCodes, $recordCodesUsedAsAxis));
    }

    private function buildErrorMessage(
        ConstraintViolationListInterface $constraintViolationList
    ): string {
        $errorMessage = '';
        foreach ($constraintViolationList as $violation) {
            $errorMessage .= sprintf("\n  - %s", $violation->getMessage());
        }

        return $errorMessage;
    }
}
