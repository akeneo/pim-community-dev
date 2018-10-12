<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\AllowedExtensionsValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ImageAllowedExtensions;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AllowedExtensionsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->beConstructedWith(['gif', 'jpg', 'png']);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AllowedExtensionsValidator::class);
    }

    function it_validates_supported_extensions(
        $context,
        ImageAllowedExtensions $constraint
    ) {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('gif,png', $constraint);
    }

    function it_validates_an_empty_value(
        $context,
        ImageAllowedExtensions $constraint
    ) {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('', $constraint);
        $this->validate(null, $constraint);
    }

    function it_does_not_validate_an_unsupported_extension(
        $context,
        ImageAllowedExtensions $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(Argument::any())
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setParameter('%extension%', 'invalid')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setParameter('%valid_extensions%', 'gif, jpg, png')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('gif,invalid', $constraint);
    }
}
