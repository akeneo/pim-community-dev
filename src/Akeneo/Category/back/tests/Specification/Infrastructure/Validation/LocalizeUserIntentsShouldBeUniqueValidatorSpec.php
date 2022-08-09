<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\SetCode;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Infrastructure\Validation\LocalizeUserIntentsShouldBeUnique;
use Akeneo\Category\Infrastructure\Validation\LocalizeUserIntentsShouldBeUniqueValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocalizeUserIntentsShouldBeUniqueValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocalizeUserIntentsShouldBeUniqueValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate(1, new Type([]));
    }

    function it_does_nothing_when_the_localize_intents_are_distinct(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate([
            new SetLabel('fr_FR', 'libelle'),
            new SetLabel('en_US', 'label'),
            new SetCode('code')
        ], new LocalizeUserIntentsShouldBeUnique());
    }

    function it_throw_an_exception_when_the_localize_intents_are_not_distinct(ExecutionContext $context, ConstraintViolationBuilderInterface $violationBuilder)
    {
        $constraint = new LocalizeUserIntentsShouldBeUnique();
        $context
            ->buildViolation($constraint->message, ['{{ locale }}' => 'same_local'])
            ->shouldBeCalledOnce()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate([
            new SetLabel('locale', 'libelle'),
            new SetLabel('same_local', 'label'),
            new SetLabel('same_local', 'title'),
            new SetCode('code')
        ], new LocalizeUserIntentsShouldBeUnique());
    }
}
