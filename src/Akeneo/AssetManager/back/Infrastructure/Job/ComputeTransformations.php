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

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindSearchableAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Akeneo\AssetManager\Infrastructure\Transformation\ComputeTransformationsExecutor;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class ComputeTransformations implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ComputeTransformationsExecutor */
    private $computeTransformationsExecutor;

    /** @var FindSearchableAssetsInterface */
    private $findSearchableAssets;

    public function __construct(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        FindSearchableAssetsInterface $findSearchableAssets
    ) {
        $this->computeTransformationsExecutor = $computeTransformationsExecutor;
        $this->findSearchableAssets = $findSearchableAssets;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $assetIdentifiers = [];

        if ($this->stepExecution->getJobParameters()->has('asset_family_identifier')) {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(
                $this->stepExecution->getJobParameters()->get('asset_family_identifier')
            );
            foreach ($this->findSearchableAssets->byAssetFamilyIdentifier($assetFamilyIdentifier) as $asset) {
                /** @var SearchableAssetItem $asset */
                $assetIdentifiers[] = AssetIdentifier::fromString($asset->identifier);
            }
        } elseif ($this->stepExecution->getJobParameters()->has('asset_identifiers')) {
            $assetIdentifiers = array_map(function (string $assetIdentifier) {
                return AssetIdentifier::fromString($assetIdentifier);
            }, $this->stepExecution->getJobParameters()->get('asset_identifiers'));
        }

        $this->computeTransformationsExecutor->execute($assetIdentifiers);
    }
}
