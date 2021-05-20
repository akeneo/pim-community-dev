<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\AssetFamily\Transformation\Exception\NonApplicableTransformationException;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationFailedException;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use Akeneo\AssetManager\Infrastructure\Transformation\TransformationExecutor;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeTransformations implements TaskletInterface, TrackableTaskletInterface
{
    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/tiff',
        'image/gif',
        'image/psd',
    ];

    private ?StepExecution $stepExecution = null;
    private FindAssetIdentifiersByAssetFamilyInterface $findIdentifiersByAssetFamily;
    private GetTransformations $getTransformations;
    private AssetRepositoryInterface $assetRepository;
    private GetOutdatedVariationSource $getOutdatedVariationSource;
    private TransformationExecutor $transformationExecutor;
    private EditAssetHandler $editAssetHandler;
    private ValidatorInterface $validator;
    private CountAssetsInterface $countAssets;
    private JobRepositoryInterface $jobRepository;
    private int $batchSize;

    /** @var TransformationCollection[] */
    private array $cachedTransformationsPerAssetFamily = [];

    public function __construct(
        FindAssetIdentifiersByAssetFamilyInterface $findIdentifiersByAssetFamily,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        TransformationExecutor $transformationExecutor,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator,
        CountAssetsInterface $countAssets,
        JobRepositoryInterface $jobRepository,
        int $batchSize
    ) {
        $this->findIdentifiersByAssetFamily = $findIdentifiersByAssetFamily;
        $this->getTransformations = $getTransformations;
        $this->assetRepository = $assetRepository;
        $this->getOutdatedVariationSource = $getOutdatedVariationSource;
        $this->transformationExecutor = $transformationExecutor;
        $this->editAssetHandler = $editAssetHandler;
        $this->validator = $validator;
        $this->countAssets = $countAssets;
        $this->jobRepository = $jobRepository;
        $this->batchSize = $batchSize;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return true;
    }

    public function execute(): void
    {
        $assetIdentifiers = [];
        $totalItems = 0;

        if ($this->stepExecution->getJobParameters()->has('asset_family_identifier')) {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(
                $this->stepExecution->getJobParameters()->get('asset_family_identifier')
            );

            $assetIdentifiers = $this->findIdentifiersByAssetFamily->find($assetFamilyIdentifier);
            $totalItems = $this->countAssets->forAssetFamily($assetFamilyIdentifier);
        } elseif ($this->stepExecution->getJobParameters()->has('asset_identifiers')) {
            $assetIdentifiers = array_map(
                fn(string $assetIdentifier): AssetIdentifier => AssetIdentifier::fromString($assetIdentifier),
                $this->stepExecution->getJobParameters()->get('asset_identifiers')
            );

            $totalItems = count($assetIdentifiers);
        }

        $this->stepExecution->setTotalItems($totalItems);
        $this->doExecute($assetIdentifiers);
    }

    /**
     * @param AssetIdentifier[] $assetIdentifiers
     */
    private function doExecute(iterable $assetIdentifiers): void
    {
        $workingDirectory = $this->stepExecution->getJobExecution()->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

        $batchCount = 0;
        foreach ($assetIdentifiers as $assetIdentifier) {
            $commands = [];
            $transformedFilesCount = 0;
            $batchCount++;

            try {
                $asset = $this->assetRepository->getByIdentifier($assetIdentifier);
            } catch (AssetNotFoundException $e) {
                $this->stepExecution->addWarning(
                    sprintf('Asset %s does not exist', (string) $assetIdentifier),
                    [],
                    new DataInvalidItem(['asset_identifier' => (string) $assetIdentifier])
                );
                continue;
            }

            $transformations = $this->getTransformations($asset->getAssetFamilyIdentifier());

            foreach ($transformations as $transformation) {
                try {
                    $sourceFile = $this->getOutdatedVariationSource->forAssetAndTransformation($asset, $transformation);
                } catch (NonApplicableTransformationException $e) {
                    $this->stepExecution->addWarning(
                        sprintf(
                            'Cannot apply transformation "%s" for asset "%s": %s',
                            $transformation->getLabel()->toString(),
                            $asset->getCode(),
                            $e->getMessage()
                        ),
                        [],
                        new DataInvalidItem($transformation->normalize())
                    );
                    continue;
                }

                if (null !== $sourceFile) {
                    if (!$this->isMimeTypeSupportedForTransformations($sourceFile)) {
                        $this->stepExecution->addWarning(
                            sprintf(
                                'Could not apply transformation "%s" on asset "%s" having a media file with mime type "%s". The supported mime types are %s',
                                $transformation->getLabel()->toString(),
                                $asset->getCode(),
                                $sourceFile->normalize()['mimeType'],
                                implode(', ', self::SUPPORTED_MIME_TYPES)
                            ),
                            [],
                            new DataInvalidItem($transformation->normalize())
                        );

                        continue;
                    }
                    try {
                        $command = $this->transformationExecutor->execute(
                            $sourceFile,
                            $asset->getAssetFamilyIdentifier(),
                            $transformation,
                            $workingDirectory
                        );

                        $violations = $this->validator->validate($command);
                        if (count($violations) > 0) {
                            throw TransformationFailedException::createFromViolationList($violations);
                        }

                        $commands[] = $command;
                        $transformedFilesCount++;
                    } catch (TransformationFailedException $e) {
                        $this->stepExecution->addWarning(
                            sprintf(
                                'Could not apply transformation "%s" on asset "%s": %s',
                                $transformation->getLabel()->toString(),
                                $asset->getCode(),
                                $e->getMessage()
                            ),
                            [],
                            new DataInvalidItem($transformation->normalize())
                        );
                        continue;
                    }
                } else {
                    $this->stepExecution->incrementSummaryInfo('skipped');
                }
            }
            if (!empty($commands)) {
                /**
                 * For now, we use the EditAssetCommand to update the transformations, even if all the subcommands
                 * are EditMediaFileTargetValueCommand. This was done this way to prevent duplication of every handlers,
                 * validators and tests.
                 * In the future, if the intention of this command changes regarding the original one, we should
                 * duplicate all the logic, but this is acceptable for now.
                 */
                ($this->editAssetHandler)(new EditAssetCommand(
                    (string)$asset->getAssetFamilyIdentifier(),
                    (string)$asset->getCode(),
                    $commands
                ));
                $this->stepExecution->incrementSummaryInfo('transformations', $transformedFilesCount);
            }

            $this->stepExecution->incrementProcessedItems();
            if ($batchCount >= $this->batchSize) {
                $this->jobRepository->updateStepExecution($this->stepExecution);
                $batchCount = 0;
            }
        }

        if ($batchCount > 0) {
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }

    private function getTransformations(AssetFamilyIdentifier $assetFamilyidentifier): TransformationCollection
    {
        if (!isset($this->cachedTransformationsPerAssetFamily[(string)$assetFamilyidentifier])) {
            $this->cachedTransformationsPerAssetFamily[(string)$assetFamilyidentifier] = $this->getTransformations->fromAssetFamilyIdentifier(
                $assetFamilyidentifier
            );
        }

        return $this->cachedTransformationsPerAssetFamily[(string)$assetFamilyidentifier];
    }

    private function isMimeTypeSupportedForTransformations(FileData $sourceFile): bool
    {
        return in_array($sourceFile->normalize()['mimeType'], self::SUPPORTED_MIME_TYPES);
    }
}
