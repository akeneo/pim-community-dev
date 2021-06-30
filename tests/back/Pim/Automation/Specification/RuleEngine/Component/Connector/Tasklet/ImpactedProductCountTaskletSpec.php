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
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
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
        BulkObjectDetacherInterface $detacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($ruleDefinitionRepo, $productRuleRunner, $saver, $detacher);

        $this->setStepExecution($stepExecution);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
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
        JobParameters $jobParameters,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper
    ) {
        $configuration = [
            'ruleIds' => [1, 2]
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('ruleIds')->willReturn($configuration['ruleIds']);

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
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
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution);

        $saver->saveAll([$ruleDefinition1, $ruleDefinition2])->shouldBeCalled();
        $detacher->detachAll([$ruleDefinition1, $ruleDefinition2])->shouldBeCalled();

        $this->execute();
    }

    function it_does_not_block_other_rules_if_a_product_count_fails(
        $ruleDefinitionRepo,
        $productRuleRunner,
        $saver,
        $detacher,
        $stepExecution,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $cursor2,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'ruleIds' => [1,2]
        ];
        $ruleDefinition1->getCode()->willReturn('rule_1');
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('ruleIds')->willReturn($configuration['ruleIds']);

        $ruleDefinitionRepo->findBy(['id' => [1,2]])->willReturn([$ruleDefinition1, $ruleDefinition2]);

        $exception = new \Exception('error message');
        $productRuleRunner->dryRun($ruleDefinition1)->shouldBeCalled()->willThrow($exception);
        $productRuleRunner->dryRun($ruleDefinition2)->shouldBeCalled()->willReturn($ruleSubjectSet2);

        $ruleSubjectSet2->getSubjectsCursor()->willReturn($cursor2);

        $cursor2->count()->willReturn(1000);

        $ruleDefinition2->setImpactedSubjectCount(1000)->shouldBeCalled()->willReturn($ruleDefinition2);

        $stepExecution->addWarning(
            'Invalid rule "rule_1": could not calculate the impacted product count. Internal error : error message',
            [],
            new DataInvalidItem(['rule_code' => $ruleDefinition1->getWrappedObject()->getCode()])
        )->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('rule_calculated')->shouldBeCalledOnce();

        $saver->saveAll([$ruleDefinition1, $ruleDefinition2])->shouldBeCalled();
        $detacher->detachAll([$ruleDefinition1, $ruleDefinition2])->shouldBeCalled();

        $this->execute();
    }
}
