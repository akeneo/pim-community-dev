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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Denormalization;

use Akeneo\ReferenceEntity\Application\Record\CreateAndEditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\Connector\EditRecordCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\MediaFile\UploadMediaFileAction;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class RecordProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private EditRecordCommandFactory $editRecordCommandFactory;
    private RecordExistsInterface $recordExists;
    private ValidatorInterface $validator;
    private FindImageAttributeCodesInterface $findImageAttributeCodes;
    private FileStorerInterface $fileStorer;
    private ?StepExecution $stepExecution = null;
    private array $indexedImageAttributeCodes = [];

    public function __construct(
        EditRecordCommandFactory $editRecordCommandFactory,
        RecordExistsInterface $recordExists,
        ValidatorInterface $validator,
        FindImageAttributeCodesInterface $findImageAttributeCodes,
        FileStorerInterface $fileStorer
    ) {
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->recordExists = $recordExists;
        $this->validator = $validator;
        $this->findImageAttributeCodes = $findImageAttributeCodes;
        $this->fileStorer = $fileStorer;
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritDoc}
     */
    public function process($item): CreateAndEditRecordCommand
    {
        Assert::isArray($item);
        Assert::notNull($item['reference_entity_identifier'] ?? null);
        Assert::notNull($item['code'] ?? null);

        $item = $this->storeMedia($item, $item['reference_entity_identifier']);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($item['reference_entity_identifier']);
        $recordCode = RecordCode::fromString($item['code']);
        $createRecordCommand = null;
        if (!$this->recordExists->withReferenceEntityAndCode($referenceEntityIdentifier, $recordCode)) {
            $createRecordCommand = new CreateRecordCommand(
                $item['reference_entity_identifier'],
                $item['code'],
                []
            );
            $violations = $this->validator->validate($createRecordCommand);
            if ($violations->count() > 0) {
                $this->skipItemWithConstraintViolations($item, $violations);
            }
        }

        $editRecordCommand = $this->editRecordCommandFactory->create($referenceEntityIdentifier, $item);
        $violations = $this->validator->validate($editRecordCommand);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $createAndEditRecordCommand = new CreateAndEditRecordCommand($createRecordCommand, $editRecordCommand);
        if ($this->stepExecution) {
            $executionContext = $this->stepExecution->getExecutionContext();
            $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
            $processedItemsBatch[$item['code']] = $createAndEditRecordCommand;
            $executionContext->put('processed_items_batch', $processedItemsBatch);
        }

        return $createAndEditRecordCommand;
    }

    private function storeMedia(array $item, string $referenceEntityIdentifier): array
    {
        if (!is_array($item['values'] ?? null)) {
            return $item;
        }

        if (!array_key_exists($referenceEntityIdentifier, $this->indexedImageAttributeCodes)) {
            $this->indexedImageAttributeCodes[$referenceEntityIdentifier] = $this->findImageAttributeCodes->find(
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier)
            );
        }

        foreach ($item['values'] as $attributeCode => $values) {
            if (!in_array($attributeCode, $this->indexedImageAttributeCodes[$referenceEntityIdentifier])) {
                continue;
            }

            foreach ($values as $key => $value) {
                if (empty($value['data'] ?? '')) {
                    continue;
                }

                try {
                    $file = $this->fileStorer->store(
                        new \SplFileInfo($value['data']),
                        UploadMediaFileAction::FILE_STORAGE_ALIAS
                    );
                    $item['values'][$attributeCode][$key]['data'] = $file->getKey();
                } catch (InvalidFile $e) {
                    $this->skipItemWithMessage($item, $e->getMessage(), $e);
                }
            }
        }

        return $item;
    }

    protected function skipItemWithConstraintViolations(
        array $item,
        ConstraintViolationListInterface $violations,
        \Exception $previousException = null
    ): void {
        $itemPosition = 0;
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
            $itemPosition = $this->stepExecution->getSummaryInfo('item_position');
        }

        throw new InvalidItemFromViolationsException(
            $violations,
            new FileInvalidItem($item, $itemPosition),
            [],
            0,
            $previousException
        );
    }

    protected function skipItemWithMessage(array $item, string $message, \Exception $previousException = null): void
    {
        $itemPosition = 0;
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
            $itemPosition = $this->stepExecution->getSummaryInfo('item_position');
        }

        throw new InvalidItemException(
            $message,
            new FileInvalidItem($item, $itemPosition),
            [],
            0,
            $previousException
        );
    }
}
