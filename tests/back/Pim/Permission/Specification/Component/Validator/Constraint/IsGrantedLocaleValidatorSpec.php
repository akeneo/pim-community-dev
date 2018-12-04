<?php

namespace Specification\Akeneo\Pim\Permission\Component\Validator\Constraint;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Validator\Constraint\IsGrantedLocale;
use Akeneo\Pim\Permission\Component\Validator\Constraint\IsGrantedLocaleValidator;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsGrantedLocaleValidatorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($localeRepository, $authorizationChecker);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsGrantedLocaleValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_is_not_valid_when_locale_is_not_granted(
        $context,
        $localeRepository,
        $authorizationChecker,
        ConstraintViolationBuilderInterface $violation
    ) {
        $fooLocale = new Locale();
        $fooLocale->setCode('en_US');

        $localeRepository->findOneByIdentifier('en_US')->willReturn($fooLocale);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $fooLocale)->willReturn(false);

        $constraint = new IsGrantedLocale();
        $context
            ->buildViolation('The locale "%locale%" is not granted.', ['%locale%' => 'en_US'])
            ->willReturn($violation);

        $violation->addViolation()->shouldBeCalled();

        $this->validate('en_US', $constraint);
    }

    function it_is_valid_when_locale_is_granted(
        $context,
        $localeRepository,
        $authorizationChecker
    ) {
        $fooLocale = new Locale();
        $fooLocale->setCode('en_US');

        $localeRepository->findOneByIdentifier('en_US')->willReturn($fooLocale);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $fooLocale)->willReturn(true);

        $constraint = new IsGrantedLocale();
        $context
            ->buildViolation()
            ->shouldNotBeCalled();

        $this->validate('en_US', $constraint);
    }
}
