<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableValidationsShouldMatch;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableValidationsShouldMatchValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TableValidationsShouldMatchValidatorSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository, ExecutionContext $context)
    {
        $this->beConstructedWith($tableConfigurationRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValidationsShouldMatchValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint_type()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'validate',
                [TableValue::value('nutrition', Table::fromNormalized([['ingredient' => 'sugar']])), new NotBlank()]
            );
    }

    function it_does_nothing_when_value_is_not_a_table_value(
        ExecutionContext $context,
        ValidatorInterface $validator,
        ContextualValidatorInterface $contextualValidator
    ) {
        $context->getValidator()->willReturn($validator);
        $validator->inContext($context)->willReturn($contextualValidator);

        $contextualValidator->validate(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new TableValidationsShouldMatch());
    }

    function it_does_nothing_when(
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContext $context,
        ValidatorInterface $validator,
        ContextualValidatorInterface $baseContextualValidator,
        ContextualValidatorInterface $contextualValidator1,
        ContextualValidatorInterface $contextualValidator2,
        ContextualValidatorInterface $contextualValidator3,
        ContextualValidatorInterface $contextualValidator4
    ) {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            ['ingredient' => 'sugar', 'quantity' => 12, 'description' => 'a description'],
            ['ingredient' => 'salt', 'quantity' => 4],
            ['ingredient' => 'garlic'],
            ['ingredient' => 'pepper', 'description' => 'another description'],
        ]));

        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['code' => 'ingredient']),
                NumberColumn::fromNormalized(['code' => 'quantity', 'validations' => ['min' => 5, 'max' => 50, 'decimals_allowed' => false]]),
                TextColumn::fromNormalized(['code' => 'description', 'validations' => ['max_length' => 15]]),
                TextColumn::fromNormalized(['code' => 'useless_description', 'validations' => ['max_length' => 20]]),
                NumberColumn::fromNormalized(['code' => 'quantity_without_validation', 'validations' => ['decimals_allowed' => true]]),
                TextColumn::fromNormalized(['code' => 'description_without_validation']),
            ])
        );

        $context->getValidator()->willReturn($validator);
        $validator->inContext($context)->willReturn($baseContextualValidator);

        $baseContextualValidator->atPath('[0].quantity')->willReturn($contextualValidator1);
        $contextualValidator1->validate(12, [
            new Range(['min' => 5, 'minMessage' => TableValidationsShouldMatch::MIN_MESSAGE]),
            new Range(['max' => 50, 'maxMessage' => TableValidationsShouldMatch::MAX_MESSAGE]),
            new Type([ 'type' => 'integer', 'message' => TableValidationsShouldMatch::DECIMALS_ALLOWED_MESSAGE]),
        ])->shouldBeCalledOnce();

        $baseContextualValidator->atPath('[0].description')->willReturn($contextualValidator2);
        $contextualValidator2->validate('a description', [
            new Length(['max' => 15, 'maxMessage' => TableValidationsShouldMatch::MAX_LENGTH_MESSAGE]),
        ])->shouldBeCalledOnce();

        $baseContextualValidator->atPath('[1].quantity')->willReturn($contextualValidator3);
        $contextualValidator3->validate(4, [
            new Range(['min' => 5, 'minMessage' => TableValidationsShouldMatch::MIN_MESSAGE]),
            new Range(['max' => 50, 'maxMessage' => TableValidationsShouldMatch::MAX_MESSAGE]),
            new Type([ 'type' => 'integer', 'message' => TableValidationsShouldMatch::DECIMALS_ALLOWED_MESSAGE]),
        ])->shouldBeCalledOnce();

        $baseContextualValidator->atPath('[3].description')->willReturn($contextualValidator4);
        $contextualValidator4->validate('another description', [
            new Length(['max' => 15, 'maxMessage' => TableValidationsShouldMatch::MAX_LENGTH_MESSAGE]),
        ])->shouldBeCalledOnce();

        $this->validate($tableValue, new TableValidationsShouldMatch());
    }
}
