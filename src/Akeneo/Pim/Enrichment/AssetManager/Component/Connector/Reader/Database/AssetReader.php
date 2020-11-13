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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Reader\Database;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class AssetReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface, TrackableItemReaderInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var FindAssetIdentifiersByAssetFamilyInterface */
    private $findAssetIdentifiersByAssetFamily;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var \Iterator */
    private $identifiers;

    /** @var bool */
    private $firstRead;

    private CountAssetsInterface $countAssets;

    public function __construct(
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        AssetRepositoryInterface $assetRepository,
        CountAssetsInterface $countAssets
    ) {
        $this->findAssetIdentifiersByAssetFamily = $findAssetIdentifiersByAssetFamily;
        $this->assetRepository = $assetRepository;
        $this->countAssets = $countAssets;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $assetFamilyIdentifier = $this->assetFamilyIdentifier();
        $this->identifiers = $this->findAssetIdentifiersByAssetFamily->find($assetFamilyIdentifier);
        $this->identifiers->rewind();
        $this->firstRead = true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $asset = null;
        if (!$this->firstRead) {
            $this->identifiers->next();
        }

        if ($this->identifiers->valid()) {
            $asset = $this->assetRepository->getByIdentifier($this->identifiers->current());
            if (null !== $asset) {
                $this->stepExecution->incrementSummaryInfo('read');
            }
        }

        $this->firstRead = false;

        return $asset;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function totalItems(): int
    {
        return $this->countAssets->forAssetFamily($this->assetFamilyIdentifier());
    }

    private function assetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return AssetFamilyIdentifier::fromString(
            $this->stepExecution->getJobParameters()->get('asset_family_identifier')
        );
    }
}
