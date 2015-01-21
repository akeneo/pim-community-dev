<?php

namespace spec\Pim\Bundle\ImportExportBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\ExecutionContextInterface;

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
        $context->addViolation()->shouldNotBeCalled();
        $this->validate(null, $constraint);
    }

    function it_invalidates_an_invalid_directory($context, $constraint)
    {
        $context->addViolation($constraint->invalidMessage)->shouldBeCalledTimes(2);
        $this->validate([], $constraint);
        $this->validate('foo', $constraint);
    }

    function it_validates_a_writable_directory($context, $constraint)
    {
        $context->addViolation()->shouldNotBeCalled();
        $this->validate(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo', $constraint);
        $this->validate(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo/bar/baz/qux.txt', $constraint);
    }

    function it_invalidates_a_non_writable_directory($context, $constraint)
    {
        $context->addViolation($constraint->message)->shouldBeCalledTimes(2);
        $this->validate('/foo.csv', $constraint);
        $this->validate('/etc/qux/baz/bar/foo.ini', $constraint);
    }
}
