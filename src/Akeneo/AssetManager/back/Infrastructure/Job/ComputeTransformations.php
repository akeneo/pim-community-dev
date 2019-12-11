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
use Akeneo\AssetManager\Domain\Query\Asset\FindSearchableAssetsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Transformation\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class ComputeTransformations implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var FindSearchableAssetsInterface */
    private $findSearchableAssets;

    /** @var GetTransformations */
    private $getTransformations;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var GetOutdatedVariationSource */
    private $getOutdatedVariationSource;

    /** @var ComputeTransformationsExecutor */
    private $computeTransformationsExecutor;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    public function __construct(
        FindSearchableAssetsInterface $findSearchableAssets,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        EditAssetHandler $editAssetHandler
    ) {
        $this->findSearchableAssets = $findSearchableAssets;
        $this->getTransformations = $getTransformations;
        $this->assetRepository = $assetRepository;
        $this->getOutdatedVariationSource = $getOutdatedVariationSource;
        $this->computeTransformationsExecutor = $computeTransformationsExecutor;
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
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(
                $this->stepExecution->getJobParameters()->get('asset_family_identifier')
            );
            foreach ($this->findSearchableAssets->byAssetFamilyIdentifier($assetFamilyIdentifier) as $asset) {
                $assetIdentifiers[] = $asset->identifier;
            }
        } elseif ($this->stepExecution->getJobParameters()->has('asset_identifiers')) {
            $assetIdentifiers = $this->stepExecution->getJobParameters()->get('asset_identifiers');
        }

        $this->doExecute($assetIdentifiers);
    }

    /**
     * @param string[] $assetIdentifiers
     *
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileTransferException
     */
    private function doExecute(array $assetIdentifiers): void
    {
        $transformationsPerAssetIdentifier = $this->getTransformations->fromAssetIdentifiers(
            array_map(
                function (string $assetIdentifier): AssetIdentifier {
                    return AssetIdentifier::fromString($assetIdentifier);
                },
                $assetIdentifiers
            )
        );

        foreach ($assetIdentifiers as $assetIdentifier) {
            $asset = $this->assetRepository->getByIdentifier(Assetidentifier::fromString($assetIdentifier));
            if (null === $asset) {
                $this->stepExecution->addError(sprintf('Asset % does not exist', $assetIdentifier));
            }

            $commands = [];
            $transformations = $transformationsPerAssetIdentifier[$assetIdentifier] ?? TransformationCollection::noTransformation();

            foreach ($transformations as $transformation) {
                try {
                    $sourceFile = $this->getOutdatedVariationSource->forAssetAndTransformation($asset, $transformation);
                } catch (NonApplicableTransformationException $e) {
                    // TODO ATR-51: add warning with exception message
                    continue;
                }

                if (null !== $sourceFile) {
                    try {
                        $commands[] = $this->computeTransformationsExecutor->execute(
                            $sourceFile,
                            $asset->getAssetFamilyIdentifier(),
                            $transformation
                        );
                    } catch (\Exception $e) {
                        // TODO ATR-51: catch the right exception types
                        // TODO: add warning
                        continue;
                    }
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
            }
        }
    }
}
