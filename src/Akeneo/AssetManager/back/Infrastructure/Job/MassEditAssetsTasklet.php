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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
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
class MassEditAssetsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution;
    private AssetQueryBuilderInterface $assetQueryBuilder;
    private Client $assetClient;
    private EditAssetHandler $editAssetsHandler;
    private JobRepositoryInterface $jobRepository;
    private int $batchSize;
    private AssetIndexerInterface $assetIndexer;
    private JobStopper $jobStopper;
    private EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry;
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    public function __construct(
        EditAssetHandler $editAssetsHandler,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobRepositoryInterface $jobRepository,
        AssetIndexerInterface $assetIndexer,
        JobStopper $jobStopper,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        int $batchSize
    ) {
        $this->editAssetsHandler = $editAssetsHandler;
        $this->assetQueryBuilder = $assetQueryBuilder;
        $this->assetClient = $assetClient;
        $this->jobRepository = $jobRepository;
        $this->assetIndexer = $assetIndexer;
        $this->jobStopper = $jobStopper;
        $this->editValueCommandFactoryRegistry = $editValueCommandFactoryRegistry;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
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

    public function execute(): void
    {
        $normalizedAssetFamilyIdentifier = $this->stepExecution->getJobParameters()->get('asset_family_identifier');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($normalizedAssetFamilyIdentifier);

        $normalizedQuery = $this->stepExecution->getJobParameters()->get('query');
        $normalizedUpdaters = $this->stepExecution->getJobParameters()->get('updaters');
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

        $assetCodesToEdit = [];
        $editAssetValueCommands = $this->getEditAssetValueCommands($assetFamilyIdentifier, $normalizedUpdaters);
        foreach ($cursor as $assetIdentifier) {
            $assetCodesToEdit[] = $assetIdentifier;

            if ($this->batchSize === count($assetCodesToEdit)) {
                if ($this->jobStopper->isStopping($this->stepExecution)) {
                    $this->jobStopper->stop($this->stepExecution);

                    break;
                }
                $this->editAssets($assetFamilyIdentifier, $assetCodesToEdit, $editAssetValueCommands);

                $assetCodesToEdit = [];
            }
        }

        if ($this->jobStopper->isStopping($this->stepExecution)) {
            $this->jobStopper->stop($this->stepExecution);
            $this->assetIndexer->refresh();

            return;
        }

        if (count($assetCodesToEdit) > 0) {
            $this->editAssets($assetFamilyIdentifier, $assetCodesToEdit, $editAssetValueCommands);
        }

        $this->assetIndexer->refresh();
    }

    private function editAssets(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodesToEdit, array $editAssetValueCommands)
    {
        try {
            foreach ($assetCodesToEdit as $assetCode) {
                $editAssetsCommand = new EditAssetCommand((string) $assetFamilyIdentifier, $assetCode, $editAssetValueCommands);
                ($this->editAssetsHandler)($editAssetsCommand);
            }

            $this->stepExecution->incrementSummaryInfo('assets', count($assetCodesToEdit));
            $this->stepExecution->incrementProcessedItems(count($assetCodesToEdit));
            $this->jobRepository->updateStepExecution($this->stepExecution);
        } catch (\Exception $exception) {
            $this->stepExecution->addWarning(
                'akeneo_assetmanager.jobs.asset_manager_mass_edit.error',
                [
                    '{{ assets }}' => (string) implode(', ', $assetCodesToEdit),
                    '{{ error }}' => $exception->getMessage()
                ],
                new DataInvalidItem(['asset_identifier' => (string) implode(', ', $assetCodesToEdit), 'error' => $exception->getMessage()])
            );
        }
    }

    private function getEditAssetValueCommands(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedUpdaters): array
    {
        $attributes = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        $editAssetValueCommands = array_map(function ($updater) use ($attributes) {
            $factory = $this->editValueCommandFactoryRegistry->getFactory($attributes[$updater['attribute']], $updater);

            return $factory->create($attributes[$updater['attribute']], $updater);
        }, $normalizedUpdaters);

        return $editAssetValueCommands;
    }
}
