<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteNamingConvention;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Infrastructure\Job\NamingConventionTasklet;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NamingConventionTaskletSpec extends ObjectBehavior
{
    public function let(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        StepExecution $stepExecution,
        CountAssetsInterface $countAssets,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith(
            $executeNamingConvention,
            $findAssetIdentifiersByAssetFamily,
            $countAssets,
            $jobRepository,
            3
        );
        $this->setStepExecution($stepExecution);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(NamingConventionTasklet::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    public function it_call_the_naming_convention_service_on_each_asset(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        \Iterator $assets,
        AssetIdentifier $assetIdentifier1,
        AssetIdentifier $assetIdentifier2,
        CountAssetsInterface $countAssets
    ) {
        $jobParameters
            ->get('asset_family_identifier')
            ->willReturn('packshot');
        $stepExecution
            ->getJobParameters()
            ->willReturn($jobParameters);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $assets->valid()->willReturn(true, true, false);
        $assets->current()->willReturn($assetIdentifier1, $assetIdentifier2);
        $assets->rewind()->shouldBeCalled();
        $assets->next()->shouldBeCalled();

        $findAssetIdentifiersByAssetFamily
            ->find(Argument::type(AssetFamilyIdentifier::class))
            ->shouldBeCalled()
            ->willReturn($assets);

        $countAssets->forAssetFamily($assetFamilyIdentifier)->shouldBeCalledOnce()->willReturn(2);
        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $stepExecution
            ->addSummaryInfo('assets', 0)
            ->shouldBeCalled();
        $executeNamingConvention
            ->executeOnAsset(Argument::type(AssetFamilyIdentifier::class), $assetIdentifier1)
            ->shouldBeCalled();
        $executeNamingConvention
            ->executeOnAsset(Argument::type(AssetFamilyIdentifier::class), $assetIdentifier2)
            ->shouldBeCalled();
        $stepExecution
            ->incrementSummaryInfo('assets')
            ->shouldBeCalledTimes(2);
        $stepExecution
            ->incrementProcessedItems()
            ->shouldBeCalledTimes(2);

        $this->execute();
    }

    public function it_report_any_errors(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        \Iterator $assets,
        AssetIdentifier $assetIdentifier,
        CountAssetsInterface $countAssets
    ) {
        $jobParameters
            ->get('asset_family_identifier')
            ->willReturn('packshot');
        $stepExecution
            ->getJobParameters()
            ->willReturn($jobParameters);
        $assetIdentifier
            ->__toString()
            ->willReturn('packshot_1');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $assets->valid()->willReturn(true, false);
        $assets->current()->willReturn($assetIdentifier);
        $assets->rewind()->shouldBeCalled();
        $assets->next()->shouldBeCalled();

        $findAssetIdentifiersByAssetFamily
            ->find(Argument::type(AssetFamilyIdentifier::class))
            ->shouldBeCalled()
            ->willReturn($assets);

        $countAssets->forAssetFamily($assetFamilyIdentifier)->shouldBeCalledOnce()->willReturn(1);

        $stepExecution->setTotalItems(1)->shouldBeCalledOnce();
        $stepExecution
            ->addSummaryInfo('assets', 0)
            ->shouldBeCalled();
        $executeNamingConvention
            ->executeOnAsset(Argument::type(AssetFamilyIdentifier::class), $assetIdentifier)
            ->shouldBeCalled()
            ->willThrow(ExecuteNamingConventionException::class);
        $stepExecution
            ->incrementSummaryInfo('assets')
            ->shouldNotBeCalled();
        $stepExecution
            ->incrementProcessedItems()
            ->shouldBeCalledTimes(1);
        $stepExecution
            ->addWarning(
                'pim_asset_manager.jobs.asset_manager_execute_naming_convention.error',
                [
                    'asset' => 'packshot_1',
                ],
                Argument::type(DataInvalidItem::class)
            )
            ->shouldBeCalled();

        $this->execute();
    }

    function it_batch_asset_naming_convention(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        CountAssetsInterface $countAssets,
        JobRepositoryInterface $jobRepository
    ) {
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $assetIdentifier1 = AssetIdentifier::fromString('assetIdentifier1');
        $assetIdentifier2 = AssetIdentifier::fromString('assetIdentifier2');
        $assetIdentifier3 = AssetIdentifier::fromString('assetIdentifier3');
        $assetIdentifier4 = AssetIdentifier::fromString('assetIdentifier4');

        $findAssetIdentifiersByAssetFamily->find($assetFamilyIdentifier)->shouldBeCalled()->willReturn(
            new \ArrayIterator([
                $assetIdentifier1,
                $assetIdentifier2,
                $assetIdentifier3,
                $assetIdentifier4,
            ])
        );

        $countAssets->forAssetFamily($assetFamilyIdentifier)->shouldBeCalledOnce()->willReturn(4);
        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $stepExecution->addSummaryInfo('assets', 0)->shouldBeCalled();
        $executeNamingConvention->executeOnAsset($assetFamilyIdentifier, $assetIdentifier1)->shouldBeCalled();
        $executeNamingConvention->executeOnAsset($assetFamilyIdentifier, $assetIdentifier2)->shouldBeCalled();
        $executeNamingConvention->executeOnAsset($assetFamilyIdentifier, $assetIdentifier3)->shouldBeCalled();
        $executeNamingConvention->executeOnAsset($assetFamilyIdentifier, $assetIdentifier4)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('assets')->shouldBeCalledTimes(4);
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(4);

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(2);

        $this->execute();
    }
}
