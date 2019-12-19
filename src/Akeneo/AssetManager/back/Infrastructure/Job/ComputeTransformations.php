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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationFailedException;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use Akeneo\AssetManager\Infrastructure\Transformation\TransformationExecutor;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class ComputeTransformations implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var FindAssetIdentifiersByAssetFamilyInterface */
    private $findIdentifiersByAssetFamily;

    /** @var GetTransformations */
    private $getTransformations;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var GetOutdatedVariationSource */
    private $getOutdatedVariationSource;

    /** @var TransformationExecutor */
    private $transformationExecutor;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    /** @var TransformationCollection[] */
    private $cachedTransformationsPerAssetFamily = [];

    public function __construct(
        FindAssetIdentifiersByAssetFamilyInterface $findIdentifiersByAssetFamily,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        TransformationExecutor $transformationExecutor,
        EditAssetHandler $editAssetHandler
    ) {
        $this->findIdentifiersByAssetFamily = $findIdentifiersByAssetFamily;
        $this->getTransformations = $getTransformations;
        $this->assetRepository = $assetRepository;
        $this->getOutdatedVariationSource = $getOutdatedVariationSource;
        $this->transformationExecutor = $transformationExecutor;
        $this->editAssetHandler = $editAssetHandler;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $assetIdentifiers = [];

        if ($this->stepExecution->getJobParameters()->has('asset_family_identifier')) {
            $assetIdentifiers = $this->findIdentifiersByAssetFamily->find(
                AssetFamilyIdentifier::fromString(
                    $this->stepExecution->getJobParameters()->get('asset_family_identifier')
                )
            );
        } elseif ($this->stepExecution->getJobParameters()->has('asset_identifiers')) {
            $assetIdentifiers = array_map(
                function (string $assetIdentifier): AssetIdentifier {
                    return AssetIdentifier::fromString($assetIdentifier);
                },
                $this->stepExecution->getJobParameters()->get('asset_identifiers')
            );
        }

        $this->doExecute($assetIdentifiers);
    }

    /**
     * @param AssetIdentifier[] $assetIdentifiers
     */
    private function doExecute(iterable $assetIdentifiers): void
    {
        foreach ($assetIdentifiers as $assetIdentifier) {
            $commands = [];
            $transformedFilesCount = 0;

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

            $transformations = $this->getTransformations($asset->getAssetFamilyIdentifier())->sortedBySource();

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
                    try {
                        $commands[] = $this->transformationExecutor->execute(
                            $sourceFile,
                            $asset->getAssetFamilyIdentifier(),
                            $transformation
                        );
                        $transformedFilesCount++;
                    } catch (TransformationFailedException $e) {
                        $this->stepExecution->addError(
                            sprintf(
                                'Could not apply transformation "%s" on asset "%s": %s',
                                $transformation->getLabel()->toString(),
                                $asset->getCode(),
                                $e->getMessage()
                            )
                        );
                        continue;
                    }
                } else {
                    $this->stepExecution->incrementSummaryInfo('skipped');
                }
            }
            if (!empty($commands)) {
                ($this->editAssetHandler)(
                    new EditAssetCommand(
                        (string)$asset->getAssetFamilyIdentifier(),
                        (string)$asset->getCode(),
                        $commands
                    )
                );
                $this->stepExecution->incrementSummaryInfo('transformations', $transformedFilesCount);
            }
        }
    }

    private function getTransformations(AssetFamilyidentifier $assetFamilyidentifier): TransformationCollection
    {
        if (!isset($this->cachedTransformationsPerAssetFamily[(string)$assetFamilyidentifier])) {
            $this->cachedTransformationsPerAssetFamily[(string)$assetFamilyidentifier] = $this->getTransformations->fromAssetFamilyIdentifier(
                $assetFamilyidentifier
            );
        }

        return $this->cachedTransformationsPerAssetFamily[(string)$assetFamilyidentifier];
    }
}
