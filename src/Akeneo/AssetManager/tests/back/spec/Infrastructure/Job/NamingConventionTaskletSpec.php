<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteNamingConvention;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Infrastructure\Job\NamingConventionTasklet;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NamingConventionTaskletSpec extends ObjectBehavior
{
    public function let(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $executeNamingConvention,
            $findAssetIdentifiersByAssetFamily
        );
        $this->setStepExecution($stepExecution);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(NamingConventionTasklet::class);
    }

    public function it_call_the_naming_convention_service_on_each_asset(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        \Iterator $assets,
        AssetIdentifier $assetIdentifier1,
        AssetIdentifier $assetIdentifier2
    ) {
        $jobParameters
            ->get('asset_family_identifier')
            ->willReturn('packshot');
        $stepExecution
            ->getJobParameters()
            ->willReturn($jobParameters);

        $assets->valid()->willReturn(true, true, false);
        $assets->current()->willReturn($assetIdentifier1, $assetIdentifier2);
        $assets->rewind()->shouldBeCalled();
        $assets->next()->shouldBeCalled();

        $findAssetIdentifiersByAssetFamily
            ->find(Argument::type(AssetFamilyIdentifier::class))
            ->shouldBeCalled()
            ->willReturn($assets);

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

        $this->execute();
    }

    public function it_report_any_errors(
        ExecuteNamingConvention $executeNamingConvention,
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        \Iterator $assets,
        AssetIdentifier $assetIdentifier
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

        $assets->valid()->willReturn(true, false);
        $assets->current()->willReturn($assetIdentifier);
        $assets->rewind()->shouldBeCalled();
        $assets->next()->shouldBeCalled();

        $findAssetIdentifiersByAssetFamily
            ->find(Argument::type(AssetFamilyIdentifier::class))
            ->shouldBeCalled()
            ->willReturn($assets);

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
}
