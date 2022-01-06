<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExist;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExistValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ChannelShouldExistValidatorSpec extends ObjectBehavior
{
    public function let(ChannelExistsWithLocaleInterface $channelExistsWithLocale, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($channelExistsWithLocale);
        $this->initialize($context);
    }

    public function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ChannelShouldExistValidator::class);
    }

    public function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new IsNull()]);
    }

    public function it_does_not_validate_a_non_string_value(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ) {
        $channelExistsWithLocale->doesChannelExist(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ChannelShouldExist());
    }

    public function it_does_not_add_a_violation_if_the_channel_exist(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ) {
        $channelExistsWithLocale->doesChannelExist('ecommerce')->shouldBeCalled()->willReturn(true);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('ecommerce', new ChannelShouldExist());
    }

    public function it_adds_a_violation_if_the_channel_do_not_exist(
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
