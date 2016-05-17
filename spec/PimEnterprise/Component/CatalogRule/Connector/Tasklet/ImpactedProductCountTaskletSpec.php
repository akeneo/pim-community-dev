<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Component\CatalogRule\Connector\Tasklet;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class ImpactedProductCountTaskletSpec extends ObjectBehavior
{
    function let(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        DryRunnerInterface $productRuleRunner,
        BulkSaverInterface $saver,
        BulkObjectDetacherInterface $detacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($ruleDefinitionRepo, $productRuleRunner, $saver, $detacher);

        $this->setStepExecution($stepExecution);
    }

    function it_executes_impacted_product_by_rules(
        $ruleDefinitionRepo,
        $productRuleRunner,
        $saver,
        $detacher,
        $stepExecution,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $cursor1,
        CursorInterface $cursor2,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'ruleIds' => [1,2]
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('ruleIds')->willReturn($configuration['ruleIds']);

        $ruleDefinitionRepo->findBy(['id' => [1,2]])->willReturn([$ruleDefinition1, $ruleDefinition2]);

        $productRuleRunner->dryRun($ruleDefinition1)->willReturn($ruleSubjectSet1);
        $productRuleRunner->dryRun($ruleDefinition2)->willReturn($ruleSubjectSet2);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($cursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($cursor2);

        $cursor1->count()->willReturn(250);
        $cursor2->count()->willReturn(1000);

        $ruleDefinition1->setImpactedSubjectCount(250)->willReturn($ruleDefinition1);
        $ruleDefinition1->setImpactedSubjectCount(1000)->willReturn($ruleDefinition2);

        $stepExecution->incrementSummaryInfo('rule_calculated')->shouldBeCalled();

        $saver->saveAll([$ruleDefinition1, $ruleDefinition2])->shouldBeCalled();
        $detacher->detachAll([$ruleDefinition1, $ruleDefinition2])->shouldBeCalled();

        $this->execute();
    }
}
