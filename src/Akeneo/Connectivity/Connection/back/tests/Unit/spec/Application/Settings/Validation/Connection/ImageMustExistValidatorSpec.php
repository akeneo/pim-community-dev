<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DoesImageExistQueryInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\ImageMustExist;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\ImageMustExistValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImageMustExistValidatorSpec extends ObjectBehavior
{
    public function let(DoesImageExistQueryInterface $imageExistQuery, ExecutionContextInterface $context): void
    {
        $this->beConstructedWith($imageExistQuery);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ImageMustExistValidator::class);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_validates_an_image_exist($imageExistQuery, $context): void
    {
        $constraint = new ImageMustExist();
        $imageExistQuery->execute('a/b/c/path.jpg')->willReturn(true);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('a/b/c/path.jpg', $constraint)->shouldReturn(null);
    }

    public function it_builds_a_violation_if_the_image_does_not_exist(
        $imageExistQuery,
        $context,
        ConstraintViolationBuilderInterface $builder
    ): void {
        $constraint = new ImageMustExist();
        $imageExistQuery->execute('not/a/good/path.jpg')->willReturn(false);

        $context->buildViolation('akeneo_connectivity.connection.connection.constraint.image.must_exist')
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('not/a/good/path.jpg', $constraint)->shouldReturn(null);
    }
}
