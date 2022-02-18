<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ReferenceEntity;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsReferenceEntityLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ReferenceEntity\ReferenceEntityShouldNotBeLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ReferenceEntity\ReferenceEntityShouldNotBeLinkedToATableColumnValidator;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReferenceEntityShouldNotBeLinkedToATableColumnValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context, IsReferenceEntityLinkedToATableColumn $isLinkedToATableColumn)
    {
        if (! class_exists(AkeneoReferenceEntityBundle::class)) {
            throw new SkippingException('ReferenceEntity are not available in this scope');
        }
        $this->beConstructedWith($isLinkedToATableColumn);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ReferenceEntityShouldNotBeLinkedToATableColumnValidator::class);
    }

    function it_fails_with_bad_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [
                new DeleteReferenceEntityCommand('brand'),
                new Type('string')
            ]
        );
    }

    function it_fails_with_bad_command()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [
                new \stdClass(),
                new ReferenceEntityShouldNotBeLinkedToATableColumn()
            ]
        );
    }

    function it_does_not_add_violation_when_reference_entity_is_not_linked_to_column(
        ExecutionContext $context,
        IsReferenceEntityLinkedToATableColumn $isLinkedToATableColumn
    ) {
        $isLinkedToATableColumn->forIdentifier('brand')->willReturn(false);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(
            new DeleteReferenceEntityCommand('brand'),
            new ReferenceEntityShouldNotBeLinkedToATableColumn()
        );
    }

    function it_adds_violation_when_reference_entity_is_linked_to_column(
        ExecutionContext                      $context,
        IsReferenceEntityLinkedToATableColumn $isLinkedToATableColumn,
        ConstraintViolationBuilderInterface   $violationBuilder
    ) {
        $isLinkedToATableColumn->forIdentifier('brand')->willReturn(true);

        $context->buildViolation(Argument::any())->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(
            new DeleteReferenceEntityCommand('brand'),
            new ReferenceEntityShouldNotBeLinkedToATableColumn()
        );
    }
}
