<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\RuleTemplateExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class LinkAssetsToProductsTaskletSpec extends ObjectBehavior
{
    public function let(RuleTemplateExecutor $ruleExecutor)
    {
        $this->beConstructedWith($ruleExecutor);
    }

    public function it_executes_the_linking_rule_of_an_asset_family(
        RuleTemplateExecutor $ruleExecutor,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->get('asset_code')->willReturn('iphone4');

        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $this->setStepExecution($stepExecution);

        $ruleExecutor->execute(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('iphone4')
        )->shouldBeCalled();

        $this->execute();
    }
}
