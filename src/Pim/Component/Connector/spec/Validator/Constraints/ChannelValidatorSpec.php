<?php

namespace spec\Pim\Component\Connector\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Validator\Constraints\Channel;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ChannelValidatorSpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository)
    {
        $this->beConstructedWith($channelRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Validator\Constraints\ChannelValidator');
    }

    function it_is_a_choice_validator()
    {
        $this->shouldHaveType('\Symfony\Component\Validator\Constraints\ChoiceValidator');
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType('\Symfony\Component\Validator\ConstraintValidatorInterface');
    }

    function it_throws_an_exception_if_there_is_no_channel_choices($channelRepository, Constraint $constraint)
    {
        $channelRepository->getLabelsIndexedByCode()->willReturn([]);

        $this
            ->shouldThrow(new ConstraintDefinitionException('No channel is set in the application'))
            ->duringValidate(Argument::any(), $constraint);
    }

    function it_does_not_validate_a_non_existent_channel(
        $channelRepository,
        Channel $constraint,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ) {
        $channelRepository->getLabelsIndexedByCode()->willReturn(['mobile' => 'mobile']);

        $context->buildViolation(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->setParameter(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->setCode(Argument::any())->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->initialize($context);
        $this->validate('Magento', $constraint);
    }
}
