<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints\ProjectIdentifier;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints\ProjectIdentifierValidator;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectIdentifierValidatorSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $projectRepository, TranslatorInterface $translator)
    {
        $this->beConstructedWith($projectRepository, $translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectIdentifierValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_adds_violation_if_the_identifier_is_invalid(
        $translator,
        $projectRepository,
        ExecutionContextInterface $context,
        ProjectIdentifier $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $this->initialize($context);

        $projectRepository->findOneByIdentifier('project_code')->willReturn(null);

        $translator->trans('teamwork_assistant.project.project_identifier', ['{{ project }}' => 'project_code'])
            ->willReturn('The project "project_code" doesn\'t exist.');

        $context->buildViolation('The project "project_code" doesn\'t exist.')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate('project_code', $constraint);
    }

    function it_does_not_adds_violation_if_the_identifier_is_valid(
        $projectRepository,
        ExecutionContextInterface $context,
        ProjectIdentifier $constraint,
        ProjectInterface $project
    ) {
        $this->initialize($context);

        $projectRepository->findOneByIdentifier('project_code')->willReturn($project);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('project_code', $constraint);
    }

    function it_only_works_with_project_project_constraint(Choice $choice)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['project_code', $choice]);
    }
}
