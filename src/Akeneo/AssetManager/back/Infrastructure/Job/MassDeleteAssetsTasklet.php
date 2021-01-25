<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\DeleteAssets\DeleteAssetsCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAssets\DeleteAssetsHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetCursor;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteAssetsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution;
    private AssetQueryBuilderInterface $assetQueryBuilder;
    private Client $assetClient;
    private DeleteAssetsHandler $deleteAssetsHandler;
    private JobRepositoryInterface $jobRepository;
    private int $batchSize;
    private AssetIndexerInterface $assetIndexer;

    public function __construct(
        DeleteAssetsHandler $deleteAssetsHandler,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobRepositoryInterface $jobRepository,
        AssetIndexerInterface $assetIndexer,
        int $batchSize
    ) {
        $this->deleteAssetsHandler = $deleteAssetsHandler;
        $this->assetQueryBuilder = $assetQueryBuilder;
        $this->assetClient = $assetClient;
        $this->jobRepository = $jobRepository;
        $this->assetIndexer = $assetIndexer;
        $this->batchSize = $batchSize;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return true;
    }

    /**
     * @TODO make this tasklet stoppable
     */

    public function execute(): void
    {
        $normalizedAssetFamilyIdentifier = $this->stepExecution->getJobParameters()->get('asset_family_identifier');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($normalizedAssetFamilyIdentifier);

        $normalizedQuery = $this->stepExecution->getJobParameters()->get('query');
        $normalizedQuery['size'] = $this->batchSize;
        $assetQuery = AssetQuery::createFromNormalized($normalizedQuery);

        $cursor = new AssetCursor($this->assetQueryBuilder, $this->assetClient, $assetQuery);
        $this->stepExecution->setTotalItems($cursor->count());

        $assetCodesToDelete = [];
        foreach ($cursor as $assetIdentifier) {
            $assetCodesToDelete[] = $assetIdentifier;

            if ($this->batchSize === count($assetCodesToDelete)) {
                $this->deleteAssets($assetFamilyIdentifier, $assetCodesToDelete);

                $assetCodesToDelete = [];
            }
        }

        if (count($assetCodesToDelete) > 0) {
            $this->deleteAssets($assetFamilyIdentifier, $assetCodesToDelete);
        }

        $this->assetIndexer->refresh();
    }

    private function deleteAssets(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodesToDelete)
    {
        try {
            $deleteAssetsCommand = new DeleteAssetsCommand((string) $assetFamilyIdentifier, $assetCodesToDelete);
            ($this->deleteAssetsHandler)($deleteAssetsCommand);
            $this->stepExecution->incrementSummaryInfo('assets', count($assetCodesToDelete));
            $this->stepExecution->incrementProcessedItems(count($assetCodesToDelete));
            $this->jobRepository->updateStepExecution($this->stepExecution);
        } catch (\Exception $exception) {
            $this->stepExecution->addWarning(
                'pim_asset_manager.jobs.asset_manager_mass_delete.error',
                [
                    'assets' => (string) implode(', ', $assetCodesToDelete),
                ],
                new DataInvalidItem(['asset_identifier' => (string) implode(', ', $assetCodesToDelete)])
            );
        }
    }
}
