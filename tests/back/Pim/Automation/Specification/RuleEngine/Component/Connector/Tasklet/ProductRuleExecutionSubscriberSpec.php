<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet;

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet\ProductRuleExecutionSubscriber;
use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetAction;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SavedSubjectsEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSet;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobInterruptedException;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductRuleExecutionSubscriberSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution, JobRepositoryInterface $jobRepository, JobStopper $jobStopper)
    {
        $this->beConstructedWith($stepExecution, $jobRepository, $jobStopper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRuleExecutionSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_rule_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(RuleEvents::PRE_EXECUTE);
        $this::getSubscribedEvents()->shouldHaveKey(RuleEvents::POST_SELECT);
        $this::getSubscribedEvents()->shouldHaveKey(RuleEvents::POST_EXECUTE);
        $this::getSubscribedEvents()->shouldHaveKey(RuleEvents::POST_SAVE_SUBJECTS);
        $this::getSubscribedEvents()->shouldHaveKey(RuleEvents::SKIP);
        $this::getSubscribedEvents()->shouldHaveKey(SkippedActionForSubjectEvent::class);
    }

    function it_updates_step_execution_before_executing_a_rule(StepExecution $stepExecution, JobRepositoryInterface $jobRepository)
    {
        $stepExecution->incrementSummaryInfo('read_rules')->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->preExecute(new GenericEvent());
    }

    function it_updates_step_execution_summary_after_selecting_rule_subjects(
        StepExecution $stepExecution,
        CursorInterface $cursor,
        JobRepositoryInterface $jobRepository
    ) {
        $cursor->count()->willReturn(3099);
        $subjectSet = new RuleSubjectSet();
        $subjectSet->setSubjectsCursor($cursor->getWrappedObject());

        $stepExecution->incrementSummaryInfo('selected_entities', 3099)->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->postSelect(new SelectedRuleEvent(new RuleDefinition(), $subjectSet));
    }

    function it_updates_step_execution_summary_after_applying_a_rule(StepExecution $stepExecution, JobRepositoryInterface $jobRepository)
    {
        $stepExecution->incrementSummaryInfo('executed_rules')->shouldBeCalled();
        $this->postExecute(new GenericEvent(new RuleDefinition()));
    }

    function it_updates_step_execution_summary_after_saving_rule_subjects(
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper,
    ) {
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $stepExecution->incrementSummaryInfo('updated_entities', 2)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->postSave(new SavedSubjectsEvent(new RuleDefinition(), [new Product(), new ProductModel()]));
    }

    function it_throws_a_job_interrupted_exception_after_saving_when_job_is_stopping(
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper,
    ) {
        $jobStopper->isStopping($stepExecution)->willReturn(true);

        $stepExecution->incrementSummaryInfo('updated_entities', 2)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->shouldThrow(JobInterruptedException::class)->during('postSave', [
            new SavedSubjectsEvent(new RuleDefinition(), [new Product(), new ProductModel()]),
        ]);
    }

    function it_adds_warnings_for_an_invalid_product(
        StepExecution $stepExecution,
        ProductInterface $product,
        JobRepositoryInterface $jobRepository
    ) {
        $rule = new RuleDefinition();
        $rule->setCode('my_rule');
        $product->getIdentifier()->willReturn('foo');
        $reasons = [
            'validation error 1',
            'validation error 2'
        ];

        $stepExecution->addWarning(<<<EOL
            Rule "my_rule": validation failed for "foo" product:
            validation error 1
            validation error 2
            EOL,
            [],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_invalid')->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->skipInvalid(new SkippedSubjectRuleEvent($rule, $product->getWrappedObject(), $reasons));
    }

    function it_adds_a_warning_if_an_action_cannot_be_applied_to_a_product(
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository
    ) {
        $product = new Product();
        $product->setIdentifier('super_shoes');

        $stepExecution->incrementSummaryInfo('read_rules')->shouldBeCalled();
        $stepExecution->addWarning(
            'Rule "my_rule": Could not apply "set" action to product "super_shoes": Invalid product data',
            [],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_invalid')->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $rule = (new RuleDefinition())->setCode('my_rule');
        $this->preExecute(new GenericEvent($rule));
        $this->skipAction(new SkippedActionForSubjectEvent(new ProductSetAction([]), $product, 'Invalid product data'));
    }
}
