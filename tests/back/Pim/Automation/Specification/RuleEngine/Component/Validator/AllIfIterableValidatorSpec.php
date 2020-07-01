<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\AllIfIterableValidator;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AllIfIterable;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class AllIfIterableValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $executionContext)
    {
        $this->initialize($executionContext);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(AllIfIterableValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['test', new IsNull()]);
    }

    function it_validates_nothing_when_value_is_not_iterable(ExecutionContextInterface $executionContext)
    {
        $executionContext->getValidator()->shouldNotBeCalled();

        $this->validate('test', $this->getConstraint());
    }

    function it_validates_all_elements_when_value_is_iterable(
        ExecutionContextInterface $executionContext,
        ValidatorInterface $validator,
        ContextualValidatorInterface $contextualValidator
    ) {
        $value = ['test1', 2];
        $executionContext->getGroup()->willReturn('group');

        $executionContext->getValidator()->shouldBeCalled()->willReturn($validator);
        $validator->inContext($executionContext)->willReturn($contextualValidator);
        $contextualValidator->validate($value, new All(([new IsNull()])), 'group')
            ->shouldBeCalled();

        $this->validate($value, $this->getConstraint());
    }

    function getConstraint(): AllIfIterable
    {
        return new AllIfIterable([new IsNull()]);
    }
}
