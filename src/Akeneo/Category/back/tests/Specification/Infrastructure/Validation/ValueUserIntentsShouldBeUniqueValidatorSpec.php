<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldBeUnique;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldBeUniqueValidator;
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
class ValueUserIntentsShouldBeUniqueValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueUserIntentsShouldBeUniqueValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate(1, new Type([]));
    }

    function it_does_nothing_when_the_value_intents_are_distinct(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate([
            new SetLabel('fr_FR', 'libelle'),
            new SetLabel('en_US', 'label'),
            new SetTextArea('uuid', 'code', 'ecommerce', 'en_US', 'value'),
            new SetTextArea('uuid', 'title', 'ecommerce', 'en_US', 'Title'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    function it_throws_an_exception_when_the_value_intents_are_not_distinct(ExecutionContext $context, ConstraintViolationBuilderInterface $violationBuilder)
    {
        $constraint = new ValueUserIntentsShouldBeUnique();
        $context
            ->buildViolation($constraint->message, ['{{ attributeCode }}' => 'same_attribute_code'])
            ->shouldBeCalledOnce()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate([
            new SetLabel('locale', 'libelle'),
            new SetLabel('locale', 'label'),
            new SetTextArea('uuid', 'same_attribute_code', 'ecommerce', 'en_US', 'value'),
            new SetTextArea('uuid-uuid', 'title', 'ecommerce', 'en_US', 'Title'),
            new SetTextArea('uuid', 'same_attribute_code', 'ecommerce', 'en_US', 'other value'),
        ], new ValueUserIntentsShouldBeUnique());
    }
}
