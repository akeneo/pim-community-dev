<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Validation\App;

use Akeneo\Apps\Domain\Validation\App\AppCodeMustBeValid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AppCodeMustBeValidSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AppCodeMustBeValid::class);
    }

    function it_does_not_build_violation_on_valid_code(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('magento', $context);
    }

    function it_adds_a_violation_when_the_code_is_empty(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('akeneo_apps.app.constraint.code.required')->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('', $context);
    }
}
