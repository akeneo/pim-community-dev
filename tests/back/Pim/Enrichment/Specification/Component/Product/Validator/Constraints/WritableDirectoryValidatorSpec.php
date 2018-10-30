<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class WritableDirectoryValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, WritableDirectory $constraint)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_does_not_validate_a_null_value($context, $constraint)
    {
        $context->buildViolation()->shouldNotBeCalled();
        $this->validate(null, $constraint);
    }

    function it_invalidates_an_invalid_directory(
        $context,
        $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context->buildViolation($constraint->invalidMessage)
            ->shouldBeCalledTimes(2)
            ->willReturn($violation);

        $this->validate([], $constraint);
        $this->validate('foo', $constraint);
    }

    function it_validates_a_writable_directory($context, $constraint)
    {
        $context->buildViolation()->shouldNotBeCalled();
        $this->validate(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo', $constraint);
        $this->validate(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo/bar/baz/qux.txt', $constraint);
    }

    function it_invalidates_a_non_writable_directory(
        $context,
        $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context->buildViolation($constraint->message)
            ->shouldBeCalledTimes(2)
            ->willReturn($violation);
        $this->validate('/foo.csv', $constraint);
        $this->validate('/etc/qux/baz/bar/foo.ini', $constraint);
    }
}
