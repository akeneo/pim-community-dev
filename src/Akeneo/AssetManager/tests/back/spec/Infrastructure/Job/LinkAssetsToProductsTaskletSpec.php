<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\LinkAssets\RuleTemplateExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetCodesByAssetFamilyInterface;
use Akeneo\AssetManager\Infrastructure\Job\LinkAssetsToProductsTasklet;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class LinkAssetsToProductsTaskletSpec extends ObjectBehavior
{
    public function let(
        RuleTemplateExecutor $ruleExecutor,
        FindAssetCodesByAssetFamilyInterface $findAssetCodesByAssetFamily,
        CountAssetsInterface $countAssets,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith($ruleExecutor, $findAssetCodesByAssetFamily, $countAssets, $jobRepository, 3);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(LinkAssetsToProductsTasklet::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    public function it_executes_the_linking_rule_of_assets(
        RuleTemplateExecutor $ruleExecutor,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->has('asset_codes')->willReturn(true);
        $jobParameters->get('asset_codes')->willReturn(['iphone4', 'iphone5']);

        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $ruleExecutor->execute(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('iphone4')
        )->shouldBeCalled();

        $ruleExecutor->execute(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('iphone5')
        )->shouldBeCalled();

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(2);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    public function it_executes_the_linking_rule_of_an_asset_family(
        RuleTemplateExecutor $ruleExecutor,
        FindAssetCodesByAssetFamilyInterface $findAssetCodesByAssetFamily,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        \Iterator $assetCodes,
        CountAssetsInterface $countAssets
    ) {
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->has('asset_codes')->willReturn(false);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $iphone4AssetCode = AssetCode::fromString('iphone4');
        $iphone5AssetCode = AssetCode::fromString('iphone5');
        $noPhoneAssetCode = AssetCode::fromString('no_phone');

        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $assetCodes->valid()->willReturn(true, true, true, false);
        $assetCodes->current()->willReturn($iphone4AssetCode, $iphone5AssetCode, $noPhoneAssetCode);
        $assetCodes->rewind()->shouldBeCalled();
        $assetCodes->next()->shouldBeCalled();

        $findAssetCodesByAssetFamily->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn($assetCodes);
        $countAssets->forAssetFamily($assetFamilyIdentifier)->shouldBeCalledOnce()->willReturn(3);

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $ruleExecutor->execute($assetFamilyIdentifier, $iphone4AssetCode)->shouldBeCalled();
        $ruleExecutor->execute($assetFamilyIdentifier, $iphone5AssetCode)->shouldBeCalled();
        $ruleExecutor->execute($assetFamilyIdentifier, $noPhoneAssetCode)->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(3);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_batch_asset_linking_rules(
        RuleTemplateExecutor $ruleExecutor,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobRepositoryInterface $jobRepository
    ) {
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->has('asset_codes')->willReturn(true);
        $jobParameters->get('asset_codes')->willReturn(['iphone4', 'iphone5', 'iphone6', 'iphone7']);

        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $ruleExecutor->execute($assetFamilyIdentifier, AssetCode::fromString('iphone4'))->shouldBeCalled();
        $ruleExecutor->execute($assetFamilyIdentifier, AssetCode::fromString('iphone5'))->shouldBeCalled();
        $ruleExecutor->execute($assetFamilyIdentifier, AssetCode::fromString('iphone6'))->shouldBeCalled();
        $ruleExecutor->execute($assetFamilyIdentifier, AssetCode::fromString('iphone7'))->shouldBeCalled();

        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(4);

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(2);

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
