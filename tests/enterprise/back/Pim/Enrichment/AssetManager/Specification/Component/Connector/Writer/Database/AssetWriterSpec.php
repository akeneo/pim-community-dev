<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\Database;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\EventAggregatorInterface as ComputeTransformationEventAggregatorInterface;
use Akeneo\AssetManager\Application\Asset\CreateAndEditAsset\CreateAndEditAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetsHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkMultipleAssetsCommand;
use Akeneo\AssetManager\Application\ShareAsset\CreateShareLink\CreateShareLinkCommandAggregator;
use Akeneo\AssetManager\Infrastructure\Job\ProductLinkRuleLauncher;
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
        CreateShareLinkCommandAggregator $createShareLinkCommandAggregator,
        StepExecution $stepExecution,
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $this->beConstructedWith(
            $createAssetHandler,
            $editAssetHandler,
            $eventAggregator,
            $computeTransformationEventAggregator,
            $createShareLinkCommandAggregator,
            $linkAssetsHandler
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
        EditAssetHandler $editAssetHandler,
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $createAssetHandler->__invoke(Argument::any())->shouldNotBeCalled();
        $editAssetHandler->__invoke(Argument::any())->shouldNotBeCalled();
        $linkAssetsHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->write([]);
    }

    function it_creates_assets(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        StepExecution $stepExecution,
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $createAssetCommand = new CreateAssetCommand('packshot', 'test', []);
        $editAssetCommand = new EditAssetCommand('packshot', 'test', []);
        $linkAssetCommand = new LinkAssetCommand();
        $linkAssetCommand->assetCode = 'test';
        $linkAssetCommand->assetFamilyIdentifier = 'packshot';
        $linkMultipleAssetsCommand = new LinkMultipleAssetsCommand();
        $linkMultipleAssetsCommand->linkAssetCommands = [$linkAssetCommand];
        $linkMultipleAssetsCommand->source = ProductLinkRuleLauncher::SOURCE_IMPORT;

        $createAssetHandler->__invoke($createAssetCommand)->shouldBeCalled();
        $editAssetHandler->__invoke($editAssetCommand)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $linkAssetsHandler->handle($linkMultipleAssetsCommand)->shouldBeCalled();

        $this->write([
            new CreateAndEditAssetCommand($createAssetCommand, $editAssetCommand),
        ]);
    }

    function it_updates_assets(
        CreateAssetHandler $createAssetHandler,
        EditAssetHandler $editAssetHandler,
        StepExecution $stepExecution,
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $editAssetCommand = new EditAssetCommand('packshot', 'test', []);

        $createAssetHandler->__invoke(Argument::any())->shouldNotBeCalled();
        $editAssetHandler->__invoke($editAssetCommand)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $linkAssetsHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->write([
            new CreateAndEditAssetCommand(null, $editAssetCommand),
        ]);
    }

    function it_links_assets_by_batch_of_100(
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $createAndEditAssetCommands = [];
        for($i = 0; $i < 150; $i++) {
            $createAssetCommand = new CreateAssetCommand('packshot', sprintf('test%s', $i), []);
            $editAssetCommand = new EditAssetCommand('packshot', sprintf('test%s', $i), []);
            $createAndEditAssetCommands[] = new CreateAndEditAssetCommand($createAssetCommand, $editAssetCommand);
        }

        $linkAssetsHandler->handle(Argument::any())->shouldBeCalledTimes(2);

        $this->write($createAndEditAssetCommands);
    }

    function it_flushes_asset_events(
        EventAggregatorInterface $eventAggregator,
        ComputeTransformationEventAggregatorInterface $computeTransformationEventAggregator,
        CreateShareLinkCommandAggregator $createShareLinkCommandAggregator,
    ) {
        $eventAggregator->flushEvents()->shouldBeCalled();
        $computeTransformationEventAggregator->flushEvents()->shouldBeCalled();
        $createShareLinkCommandAggregator->dispatchCommands()->shouldBeCalled();

        $this->flush();
    }
}
