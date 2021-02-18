<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\Database;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\EventAggregatorInterface as ComputeTransformationEventAggregatorInterface;
use Akeneo\AssetManager\Application\Asset\CreateAndEditAsset\CreateAndEditAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\Database\AssetWriter;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetWriterSpec extends ObjectBehavior
{
    function let(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        EventAggregatorInterface $eventAggregator,
        ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $createAssetHandler,
            $editAssetHandler,
            $eventAggregator,
            $computeTransformationEventAggregator
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_an_item_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_is_a_database_asset_writer()
    {
        $this->shouldHaveType(AssetWriter::class);
    }

    function it_is_flushable()
    {
        $this->shouldImplement(FlushableInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetWriter::class);
    }

    function it_does_nothing_if_the_commands_are_empty(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler
    ) {
        $createAssetHandler->__invoke(Argument::any())->shouldNotBeCalled();
        $editAssetHandler->__invoke(Argument::any())->shouldNotBeCalled();

        $this->write([]);
    }

    function it_creates_assets(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        StepExecution $stepExecution
    ) {
        $createAssetCommand = new CreateAssetCommand('packshot', 'test', []);
        $editAssetCommand = new EditAssetCommand('packshot', 'test', []);

        $createAssetHandler->__invoke($createAssetCommand)->shouldBeCalled();
        $editAssetHandler->__invoke($editAssetCommand)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $this->write([
            new CreateAndEditAssetCommand($createAssetCommand, $editAssetCommand),
        ]);
    }

    function it_updates_assets(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        StepExecution $stepExecution
    ) {
        $editAssetCommand = new EditAssetCommand('packshot', 'test', []);

        $createAssetHandler->__invoke(Argument::any())->shouldNotBeCalled();
        $editAssetHandler->__invoke($editAssetCommand)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write([
            new CreateAndEditAssetCommand(null, $editAssetCommand),
        ]);
    }

    function it_flushes_asset_events(
        EventAggregatorInterface $eventAggregator,
        ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator
    ) {
        $eventAggregator->flushEvents()->shouldBeCalled();
        $computeTransformationEventAggregator->flushEvents()->shouldBeCalled();

        $this->flush();
    }
}
