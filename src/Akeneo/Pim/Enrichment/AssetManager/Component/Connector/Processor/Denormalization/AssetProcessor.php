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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Denormalization;

use Akeneo\AssetManager\Application\Asset\CreateAndEditAsset\CreateAndEditAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\Connector\EditAssetCommandFactory;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindMediaFileAttributeCodesInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
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

final class AssetProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private EditAssetCommandFactory $editAssetCommandFactory;
    private AssetExistsInterface $assetExists;
    private ValidatorInterface $validator;
    private FindMediaFileAttributeCodesInterface $findMediaFileAttributeCodes;
    private FileStorerInterface $fileStorer;
    private ?StepExecution $stepExecution = null;
    private array $indexedImageAttributeCodes = [];

    public function __construct(
        EditAssetCommandFactory $editAssetCommandFactory,
        AssetExistsInterface $assetExists,
        ValidatorInterface $validator,
        FindMediaFileAttributeCodesInterface $findMediaFileAttributeCodes,
        FileStorerInterface $fileStorer
    ) {
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->assetExists = $assetExists;
        $this->validator = $validator;
        $this->findMediaFileAttributeCodes = $findMediaFileAttributeCodes;
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
    public function process($item): CreateAndEditAssetCommand
    {
        Assert::isArray($item);
        Assert::notNull($item['asset_family_identifier'] ?? null);
        Assert::notNull($item['code'] ?? null);

        $item = $this->storeMedia($item, $item['asset_family_identifier']);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($item['asset_family_identifier']);
        $assetCode = AssetCode::fromString($item['code']);
        $createAssetCommand = null;
        if (!$this->assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)) {
            $createAssetCommand = new CreateAssetCommand(
                $item['asset_family_identifier'],
                $item['code'],
                []
            );
            $violations = $this->validator->validate($createAssetCommand);
            if ($violations->count() > 0) {
                $this->skipItemWithConstraintViolations($item, $violations);
            }
        }

        $editAssetCommand = $this->editAssetCommandFactory->create($assetFamilyIdentifier, $item);
        $violations = $this->validator->validate($editAssetCommand);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $createAndEditAssetCommand = new CreateAndEditAssetCommand($createAssetCommand, $editAssetCommand);
        if ($this->stepExecution) {
            $executionContext = $this->stepExecution->getExecutionContext();
            $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
            $processedItemsBatch[$item['code']] = $createAndEditAssetCommand;
            $executionContext->put('processed_items_batch', $processedItemsBatch);
        }

        return $createAndEditAssetCommand;
    }

    private function storeMedia(array $item, string $assetFamilyIdentifier): array
    {
        if (!is_array($item['values'] ?? null)) {
            return $item;
        }

        if (!array_key_exists($assetFamilyIdentifier, $this->indexedImageAttributeCodes)) {
            $this->indexedImageAttributeCodes[$assetFamilyIdentifier] = $this->findMediaFileAttributeCodes->find(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier)
            );
        }

        foreach ($item['values'] as $attributeCode => $values) {
            if (!in_array($attributeCode, $this->indexedImageAttributeCodes[$assetFamilyIdentifier])) {
                continue;
            }

            foreach ($values as $key => $value) {
                if (empty($value['data'] ?? '')) {
                    continue;
                }

                try {
                    $file = $this->fileStorer->store(
                        new \SplFileInfo($value['data']),
                        Storage::FILE_STORAGE_ALIAS
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
