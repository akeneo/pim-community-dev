<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class ImpactedProductCountTaskletSpec extends ObjectBehavior
{
    function let(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        DryRunnerInterface $productRuleRunner,
        BulkSaverInterface $saver,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution,
        JobRepositoryInterface  $jobRepository,
        JobStopper $jobStopper
    ) {
        $this->beConstructedWith($ruleDefinitionRepo, $productRuleRunner, $saver, $cacheClearer, $jobRepository, $jobStopper);

        $this->setStepExecution($stepExecution);
    }

    function it_executes_impacted_product_by_rules(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        DryRunnerInterface $productRuleRunner,
        BulkSaverInterface $saver,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $cursor1,
        CursorInterface $cursor2,
        JobParameters $jobParameters,
        JobRepositoryInterface  $jobRepository,
        JobStopper $jobStopper
    ) {
        $configuration = [
            'ruleIds' => [1, 2]
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('ruleIds')->willReturn($configuration['ruleIds']);

        $ruleDefinitionRepo->findBy(['id' => [1, 2]])->willReturn([$ruleDefinition1, $ruleDefinition2]);

        $productRuleRunner->dryRun($ruleDefinition1)->willReturn($ruleSubjectSet1);
        $productRuleRunner->dryRun($ruleDefinition2)->willReturn($ruleSubjectSet2);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($cursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($cursor2);

        $cursor1->count()->willReturn(250);
        $cursor2->count()->willReturn(1000);

        $ruleDefinition1->setImpactedSubjectCount(250)->willReturn($ruleDefinition1);
        $ruleDefinition1->setImpactedSubjectCount(1000)->willReturn($ruleDefinition2);

        $stepExecution->incrementSummaryInfo('rule_calculated')->shouldBeCalled();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution);

        $saver->saveAll([$ruleDefinition1, $ruleDefinition2])->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    public function it_counts_the_number_of_rules_it_will_process(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $configuration = ['ruleIds' => [1, 2]];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('ruleIds')->willReturn($configuration['ruleIds']);

        $this->totalItems()->shouldReturn(2);
    }
}
