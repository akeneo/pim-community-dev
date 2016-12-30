<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\Import;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\Exception\InvalidItemFromViolationsException;
use PimEnterprise\Component\ActivityManager\Builder\ProjectBuilderInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Job\Import\ProjectProcessor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $projectRepository,
        ProjectBuilderInterface $projectBuilder,
        ObjectUpdaterInterface $projectUpdater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($projectRepository, $projectBuilder, $projectUpdater, $validator, $objectDetacher);

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\PimEnterprise\Component\ActivityManager\Job\Import\ProjectProcessor::class);
    }

    function it_is_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_is_step_execution_aware()
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_creates_a_project(
        $projectRepository,
        $projectBuilder,
        $validator,
        ConstraintViolationListInterface $constraintViolationList,
        ProjectInterface $project
    ) {
        $projectRepository->findOneByIdentifier('my-project-ecommerce-fr-fr')->willReturn(null);
        $projectBuilder->build([
            'label' => 'My project',
            'locale' => 'fr_Fr',
            'channel' => 'ecommerce',
        ])->willReturn($project);

        $validator->validate($project)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $this->process([
            'label' => 'My project',
            'locale' => 'fr_Fr',
            'channel' => 'ecommerce',
        ])->shouldReturn($project);
    }

    function it_updates_a_project(
        $projectRepository,
        $projectUpdater,
        $validator,
        ConstraintViolationListInterface $constraintViolationList,
        ProjectInterface $project
    ) {
        $projectRepository->findOneByIdentifier('my-project-ecommerce-fr-fr')->willReturn($project);

        $projectUpdater->update($project, [
            'label' => 'My project',
            'locale' => 'fr_Fr',
            'channel' => 'ecommerce',
        ])->willReturn($project);

        $validator->validate($project)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $this->process([
            'label' => 'My project',
            'locale' => 'fr_Fr',
            'channel' => 'ecommerce'
        ])->shouldReturn($project);
    }

    function it_detaches_and_adds_constraint_violation_when_data_are_invalid(
        $projectRepository,
        $validator,
        $projectUpdater,
        $objectDetacher,
        $stepExecution,
        ConstraintViolationListInterface $constraintViolationList,
        ProjectInterface $project
    ) {
        $projectRepository->findOneByIdentifier('my-project-ecommerce-fr-fr')->willReturn($project);

        $projectUpdater->update($project, Argument::type('array'))->shouldBeCalled();

        $validator->validate($project)->willReturn($constraintViolationList);
        $constraintViolationList->rewind()->shouldBeCalled();
        $constraintViolationList->valid()->shouldBeCalled();
        $constraintViolationList->count()->willReturn(1);

        $objectDetacher->detach($project)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->willReturn(1);

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [[
            'label' => 'My project',
            'locale' => 'fr_Fr',
            'channel' => 'ecommerce',
        ]]);
    }
}
