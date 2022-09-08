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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\Database;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\EventAggregatorInterface as ComputeTransformationEventAggregatorInterface;
use Akeneo\AssetManager\Application\Asset\CreateAndEditAsset\CreateAndEditAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetsHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkMultipleAssetsCommand;
use Akeneo\AssetManager\Domain\Exception\AssetAlreadyExistsError;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

final class AssetWriter implements ItemWriterInterface, StepExecutionAwareInterface, FlushableInterface
{
    private const LINK_ASSETS_BATCH_SIZE = 100;

    private CreateAssetHandler $createAssetHandler;
    private EditAssetHandler $editAssetHandler;
    private EventAggregatorInterface $eventAggregator;
    private ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator;
    private ?StepExecution $stepExecution = null;
    private LinkAssetsHandler $linkAssetsHandler;

    public function __construct(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        EventAggregatorInterface $eventAggregator,
        ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator,
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $this->createAssetHandler = $createAssetHandler;
        $this->editAssetHandler = $editAssetHandler;
        $this->eventAggregator = $eventAggregator;
        $this->computeTransformationEventAggregator = $computeTransformationEventAggregator;
        $this->linkAssetsHandler = $linkAssetsHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $createAndEditAssetCommands): void
    {
        if (0 === count($createAndEditAssetCommands)) {
            return;
        }

        $linkAssetCommands = [];

        foreach ($createAndEditAssetCommands as $createAndEditAssetCommand) {
            Assert::isInstanceOf($createAndEditAssetCommand, CreateAndEditAssetCommand::class);

            $isCreateAssetCommand = null !== $createAndEditAssetCommand->createAssetCommand;

            if ($isCreateAssetCommand) {
                try {
                    ($this->createAssetHandler)($createAndEditAssetCommand->createAssetCommand);
                } catch (AssetAlreadyExistsError) {
                    $isCreateAssetCommand = false;
                }
            }

            ($this->editAssetHandler)($createAndEditAssetCommand->editAssetCommand);

            if ($isCreateAssetCommand) {
                $linkAssetCommand = new LinkAssetCommand();
                $linkAssetCommand->assetCode = $createAndEditAssetCommand->createAssetCommand->code;
                $linkAssetCommand->assetFamilyIdentifier = $createAndEditAssetCommand->createAssetCommand->assetFamilyIdentifier;
                $linkAssetCommands[] = $linkAssetCommand;
            }

            $this->stepExecution?->incrementSummaryInfo(
                $isCreateAssetCommand ? 'create' : 'process'
            );

            if (self::LINK_ASSETS_BATCH_SIZE === count($linkAssetCommands)) {
                $this->linkAssets($linkAssetCommands);
                $linkAssetCommands = [];
            }
        }

        if (0 < count($linkAssetCommands)) {
            $this->linkAssets($linkAssetCommands);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->eventAggregator->flushEvents();
        $this->computeTransformationEventAggregator->flushEvents();
    }

    private function linkAssets(array $linkAssetCommands): void
    {
        $linkMultipleAssetsCommand = new LinkMultipleAssetsCommand();
        $linkMultipleAssetsCommand->linkAssetCommands = $linkAssetCommands;
        $this->linkAssetsHandler->handle($linkMultipleAssetsCommand);
    }
}
