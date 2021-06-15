<?php

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExistValidator;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExist;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ChannelShouldExistValidatorSpec extends ObjectBehavior
{
    function let(ChannelExistsWithLocaleInterface $channelExistsWithLocale, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($channelExistsWithLocale);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ChannelShouldExistValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ) {
        $channelExistsWithLocale->doesChannelExist(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ChannelShouldExist());
    }

    function it_does_not_add_a_violation_if_the_channel_exist(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ) {
        $channelExistsWithLocale->doesChannelExist('ecommerce')->shouldBeCalled()->willReturn(true);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('ecommerce', new ChannelShouldExist());
    }

    function it_adds_a_violation_if_the_channel_do_not_exist(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ChannelShouldExist();
        $channelExistsWithLocale->doesChannelExist('non_existent_channel')->shouldBeCalled()->willReturn(false);
        $context->buildViolation(
            'akeneo.tailored_export.validation.channel.should_exist',
            [
                '{{ channel_code }}' => 'non_existent_channel',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('non_existent_channel', $constraint);
    }
}
