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
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetCursor;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
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
    private JobStopper $jobStopper;

    public function __construct(
        DeleteAssetsHandler $deleteAssetsHandler,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobRepositoryInterface $jobRepository,
        AssetIndexerInterface $assetIndexer,
        JobStopper $jobStopper,
        int $batchSize
    ) {
        $this->deleteAssetsHandler = $deleteAssetsHandler;
        $this->assetQueryBuilder = $assetQueryBuilder;
        $this->assetClient = $assetClient;
        $this->jobRepository = $jobRepository;
        $this->assetIndexer = $assetIndexer;
        $this->batchSize = $batchSize;
        $this->jobStopper = $jobStopper;
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
        $normalizedAssetFamilyIdentifier = $this->stepExecution->getJobParameters()->get('asset_family_identifier');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($normalizedAssetFamilyIdentifier);

        $normalizedQuery = $this->stepExecution->getJobParameters()->get('query');
        $channel = ChannelReference::createFromNormalized($normalizedQuery['channel']);
        $locale = LocaleReference::createFromNormalized($normalizedQuery['locale']);
        $filters = $normalizedQuery['filters'];

        $assetQuery = AssetQuery::createWithSearchAfter(
            $assetFamilyIdentifier,
            $channel,
            $locale,
            $this->batchSize,
            null,
            $filters
        );

        $cursor = new AssetCursor($this->assetQueryBuilder, $this->assetClient, $assetQuery);
        $this->stepExecution->setTotalItems($cursor->count());

        $assetCodesToDelete = [];
        foreach ($cursor as $assetIdentifier) {
            $assetCodesToDelete[] = $assetIdentifier;

            if ($this->batchSize === count($assetCodesToDelete)) {
                if ($this->jobStopper->isStopping($this->stepExecution)) {
                    $this->jobStopper->stop($this->stepExecution);

                    break;
                }
                $this->deleteAssets($assetFamilyIdentifier, $assetCodesToDelete);

                $assetCodesToDelete = [];
            }
        }

        if ($this->jobStopper->isStopping($this->stepExecution)) {
            $this->jobStopper->stop($this->stepExecution);
            $this->assetIndexer->refresh();

            return;
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
                'akeneo_assetmanager.jobs.asset_manager_mass_delete.error',
                [
                    '{{ assets }}' => (string) implode(', ', $assetCodesToDelete),
                    '{{ error }}' => $exception->getMessage()
                ],
                new DataInvalidItem(['asset_identifier' => (string) implode(', ', $assetCodesToDelete), 'error' => $exception->getMessage()])
            );
        }
    }
}
