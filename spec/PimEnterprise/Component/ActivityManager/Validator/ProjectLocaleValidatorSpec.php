<?php

namespace spec\PimEnterprise\Component\ActivityManager\Validator;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Validator\ProjectLocaleValidator;
use PimEnterprise\Component\ActivityManager\Validator\ProjectLocale;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProjectLocaleValidatorSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectLocaleValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_adds_violation_if_the_locale_does_not_belong_the_channel(
        $translator,
        ProjectInterface $project,
        LocaleInterface $locale,
        ChannelInterface $channel,
        ExecutionContextInterface $context,
        ProjectLocale $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $this->initialize($context);

        $project->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('fr_FR');
        $project->getChannel()->willReturn($channel);
        $locale->hasChannel($channel)->willReturn(false);

        $translator->trans('activity_manager.project.project_locale', ['{{ locale }}' => 'fr_FR'])
            ->willReturn('The project locale "fr_FR" must not be disable.');

        $context->buildViolation('The project locale "fr_FR" must not be disable.')
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($project, $constraint);
    }

    function it_does_not_adds_violation_if_the_locale_belongs_the_channel(
        ProjectInterface $project,
        LocaleInterface $locale,
        ChannelInterface $channel,
        ExecutionContextInterface $context,
        ProjectLocale $constraint
    ) {
        $this->initialize($context);

        $project->getLocale()->willReturn($locale);
        $project->getChannel()->willReturn($channel);
        $locale->hasChannel($channel)->willReturn(true);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($project, $constraint);
    }

    function it_only_works_with_project_locale_constraint(Choice $choice, ProjectInterface $project)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$project, $choice]);
    }

    function it_only_works_with_project(ProjectLocale $projectLocale, ProductInterface $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $projectLocale]);
    }
}
