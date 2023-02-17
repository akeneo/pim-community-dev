<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\PropertyProcessShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\PropertyProcessShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyProcessShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $globalValidator,
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void
    {
        $this->beConstructedWith($globalValidator);
        $this->initialize($context);

        $globalValidator->inContext($context)->willReturn($validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PropertyProcessShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'no'], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $validator->validate(Argument::any(), Argument::type(Collection::class))->shouldNotBeCalled();

        $this->validate(new \stdClass(), new PropertyProcessShouldBeValid());
    }

    public function it_should_not_validate_a_process_without_type(
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void
    {
        $process = [];
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $validator->validate(Argument::any(), Argument::type(Collection::class))->shouldNotBeCalled();

        $this->validate($process, new PropertyProcessShouldBeValid());
    }

    public function it_should_validate_a_type_no_process(
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void
    {
        $process = ['type' => Process::PROCESS_TYPE_NO];
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $validator->validate($process, Argument::type(Collection::class))->shouldBeCalledOnce();

        $this->validate($process, new PropertyProcessShouldBeValid());
    }

    public function it_should_validate_a_type_truncate_process(
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void
    {
        $process = ['type' => Process::PROCESS_TYPE_TRUNCATE];
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();
        $validator->validate($process, Argument::type(Collection::class))->shouldBeCalledOnce();

        $this->validate($process, new PropertyProcessShouldBeValid());
    }
}
