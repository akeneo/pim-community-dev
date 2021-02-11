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
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

final class AssetWriter implements ItemWriterInterface, StepExecutionAwareInterface, FlushableInterface
{
    private CreateAssetHandler $createAssetHandler;
    private EditAssetHandler $editAssetHandler;
    private EventAggregatorInterface $eventAggregator;
    /** @pull-up master: remove null here and in the rest of the file */
    private ?ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator;
    private ?StepExecution $stepExecution = null;

    public function __construct(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        EventAggregatorInterface $eventAggregator,
        ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator = null
    ) {
        $this->createAssetHandler = $createAssetHandler;
        $this->editAssetHandler = $editAssetHandler;
        $this->eventAggregator = $eventAggregator;
        $this->computeTransformationEventAggregator = $computeTransformationEventAggregator;
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $createAndEditAssetCommands): void
    {
        if (0 === count($createAndEditAssetCommands)) {
            return;
        }

        foreach ($createAndEditAssetCommands as $createAndEditAssetCommand) {
            Assert::isInstanceOf($createAndEditAssetCommand, CreateAndEditAssetCommand::class);

            if ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo(
                    $createAndEditAssetCommand->createAssetCommand ? 'create' : 'process'
                );
            }

            if ($createAndEditAssetCommand->createAssetCommand) {
                ($this->createAssetHandler)($createAndEditAssetCommand->createAssetCommand);
            }

            ($this->editAssetHandler)($createAndEditAssetCommand->editAssetCommand);
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
        if (null !== $this->computeTransformationEventAggregator) {
            $this->computeTransformationEventAggregator->flushEvents();
        }
    }
}
