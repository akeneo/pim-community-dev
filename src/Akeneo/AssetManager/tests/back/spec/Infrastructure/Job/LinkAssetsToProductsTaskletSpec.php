<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\LinkAssets\RuleTemplateExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetCodesByAssetFamilyInterface;
use Akeneo\AssetManager\Infrastructure\Job\LinkAssetsToProductsTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class LinkAssetsToProductsTaskletSpec extends ObjectBehavior
{
    public function let(
        RuleTemplateExecutor $ruleExecutor,
        FindAssetCodesByAssetFamilyInterface $findAssetCodesByAssetFamily
    ) {
        $this->beConstructedWith($ruleExecutor, $findAssetCodesByAssetFamily);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(LinkAssetsToProductsTasklet::class);
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

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    public function it_executes_the_linking_rule_of_an_asset_family(
        RuleTemplateExecutor $ruleExecutor,
        FindAssetCodesByAssetFamilyInterface $findAssetCodesByAssetFamily,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->has('asset_codes')->willReturn(false);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $findAssetCodesByAssetFamily->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn(
            new \ArrayIterator([
                AssetCode::fromString('iphone4'),
                AssetCode::fromString('iphone5'),
                AssetCode::fromString('no_phone'),
            ])
        );

        $ruleExecutor->execute(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('iphone4')
        )->shouldBeCalled();
        $ruleExecutor->execute(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('iphone5')
        )->shouldBeCalled();
        $ruleExecutor->execute(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('no_phone')
        )->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
