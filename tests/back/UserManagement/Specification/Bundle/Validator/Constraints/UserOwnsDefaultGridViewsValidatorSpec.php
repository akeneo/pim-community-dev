<?php

namespace Specification\Akeneo\UserManagement\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Bundle\Validator\Constraints\UserOwnsDefaultGridViews;
use Akeneo\UserManagement\Bundle\Validator\Constraints\UserOwnsDefaultGridViewsValidator;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserOwnsDefaultGridViewsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserOwnsDefaultGridViewsValidator::class);
    }

    function it_throws_an_exception_if_the_constraint_is_not_valid(UserInterface $user)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$user, new NotBlank()]);
    }

    function it_only_validates_users(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('test', new UserOwnsDefaultGridViews());
    }

    function it_does_not_build_a_violation_if_the_view_is_public(
        ExecutionContext $context,
        UserInterface $user,
        DatagridView $view
    ) {
        $view->isPublic()->willReturn(true);
        $user->getDefaultGridViews()->willReturn(new ArrayCollection([$view->getWrappedObject()]));

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($user, new UserOwnsDefaultGridViews());
    }

    function it_does_not_build_a_violation_if_the_private_view_belongs_to_the_user(
        ExecutionContext $context,
        UserInterface $user,
        DatagridView $view
    ) {
        $view->isPublic()->willReturn(false);
        $view->getOwner()->willReturn($user);
        $user->getDefaultGridViews()->willReturn(new ArrayCollection([$view->getWrappedObject()]));

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($user, new UserOwnsDefaultGridViews());
    }

    function it_builds_a_violation_if_the_private_view_does_not_belong_to_the_user(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        UserInterface $user,
        UserInterface $otherUser,
        DatagridView $view
    ) {
        $view->isPublic()->willReturn(false);
        $view->getOwner()->willReturn($otherUser);
        $view->getDatagridAlias()->willReturn('product-grid');
        $view->getLabel()->willReturn('My private view');
        $user->getDefaultGridViews()->willReturn(new ArrayCollection([$view->getWrappedObject()]));
        $user->getUsername()->willReturn('julia');

        $context->buildViolation(
            Argument::type('string'),
            [
                '{{ label }}' => 'My private view',
                '{{ username }}' => 'julia',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('default_product_grid_view')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('My private view')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($user, new UserOwnsDefaultGridViews());
    }
}
