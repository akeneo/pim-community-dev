<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Validation\App;

use Akeneo\Apps\Domain\Validation\App\AppImageMustBeValid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AppImageMustBeValidSpec extends ObjectBehavior
{
    public function it_is_an_app_image_validator(): void
    {
        $this->shouldHaveType(AppImageMustBeValid::class);
    }

    public function it_does_not_build_a_violation_if_the_image_is_valid(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('a/b/c/path.jpg', $context);
    }

    public function it_does_not_build_a_violation_if_the_image_is_null(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(null, $context);
    }

    public function it_builds_a_violation_if_image_is_not_valid(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('akeneo_apps.app.constraint.image.not_empty')->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('', $context);
    }
}
