<?php

namespace spec\PimEnterprise\Component\ActivityManager\Validator;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Validator\ProjectDueDate;
use PimEnterprise\Component\ActivityManager\Validator\ProjectDueDateValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProjectDueDateValidatorSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectDueDateValidator::class);
    }

    function it_adds_violation_if_the_due_date_is_in_the_past(
        $translator,
        ProjectInterface $project,
        ExecutionContextInterface $context,
        ProjectDueDate $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $this->initialize($context);

        $project->getId()->willReturn(null);
        $project->getDueDate()->willReturn(new \DateTime('2016-12-12'));


        $translator->trans('activity_manager.project.project_due_date')
            ->willReturn('You can\'t select a date in the past.');

        $context->buildViolation('You can\'t select a date in the past.')
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($project, $constraint);
    }

    function it_does_not_adds_violation_the_due_date_is_not_in_the_past(
        ProjectInterface $project,
        ExecutionContextInterface $context,
        ProjectDueDate $constraint
    ) {
        $this->initialize($context);

        $project->getId()->willReturn(null);
        $project->getDueDate()->willReturn(new \DateTime('2116-12-12'));

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($project, $constraint);
    }

    function it_does_not_adds_violation_if_we_edit_the_due_date(
        ProjectInterface $project,
        ExecutionContextInterface $context,
        ProjectDueDate $constraint
    ) {
        $this->initialize($context);

        $project->getId()->willReturn(11);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($project, $constraint);
    }

    function it_only_works_with_project_locale_constraint(Choice $choice, ProjectInterface $project)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$project, $choice]);
    }

    function it_only_works_with_project(ProjectDueDate $projectDueDate, ProductInterface $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $projectDueDate]);
    }
}
