<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\API\Query\GetEditableLocaleCodes;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeEditableByUser;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeEditableByUserValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleShouldBeEditableByUserValidatorSpec extends ObjectBehavior
{
    function let(GetEditableLocaleCodes $getEditableLocaleCodes, ExecutionContext $context)
    {
        $this->beConstructedWith($getEditableLocaleCodes);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleShouldBeEditableByUserValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'foo');

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$command, new Type([])]);
    }

    function it_throws_an_exception_with_a_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [new \stdClass(), new LocaleShouldBeEditableByUser([])]);
    }

    function it_validates_when_the_locale_is_editable_by_the_user(
        ExecutionContext $context,
        getEditableLocaleCodes $getEditableLocaleCodes
    ) {
        $userIntent = new class implements ValueUserIntent {
            public function attributeCode(): string
            {
                return 'a_text';
            }
            public function value(): mixed
            {
                return 'new value';
            }
            public function localeCode(): ?string
            {
                return 'en_US';
            }
            public function channelCode(): ?string
            {
                return null;
            }
        };
        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'product_identifier', valuesUserIntent: [$userIntent]);

        $getEditableLocaleCodes->forUserId(1)->willReturn(['en_US', 'fr_FR']);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($command, new LocaleShouldBeEditableByUser());
    }

    function it_adds_a_violation_when_the_locale_is_not_editable_for_the_user(
        ExecutionContext $context,
        getEditableLocaleCodes $getEditableLocaleCodes,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new LocaleShouldBeEditableByUser();

        $userIntent = new class implements ValueUserIntent {
            public function attributeCode(): string
            {
                return 'a_text';
            }
            public function value(): mixed
            {
                return 'new value';
            }
            public function localeCode(): ?string
            {
                return 'de_DE';
            }
            public function channelCode(): ?string
            {
                return null;
            }
        };

        $command = new UpsertProductCommand(userId: 1, productIdentifier: 'product_identifier', valuesUserIntent: [$userIntent]);

        $getEditableLocaleCodes->forUserId(1)->willReturn(['en_US', 'fr_FR']);
        $context->buildViolation($constraint->message, ['{{ locale_code }}' => 'de_DE'])
            ->shouldBeCalledOnce()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($command, new LocaleShouldBeEditableByUser());
    }

    function it_adds_a_violation_for_every_user_intent_for_which_the_locale_is_not_editable_for_the_user(
        ExecutionContext $context,
        getEditableLocaleCodes $getEditableLocaleCodes,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new LocaleShouldBeEditableByUser();

        $userIntent1 = new class implements ValueUserIntent {
            public function attributeCode(): string
            {
                return 'a_text';
            }
            public function value(): mixed
            {
                return 'new value';
            }
            public function localeCode(): ?string
            {
                return 'de_DE';
            }
            public function channelCode(): ?string
            {
                return null;
            }
        };

        $userIntent2 = new class implements ValueUserIntent {
            public function attributeCode(): string
            {
                return 'a_text';
            }
            public function value(): mixed
            {
                return 'new value';
            }
            public function localeCode(): ?string
            {
                return 'en_GB';
            }
            public function channelCode(): ?string
            {
                return null;
            }
        };

        $command = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'product_identifier',
            valuesUserIntent: [$userIntent1, $userIntent2]
        );

        $getEditableLocaleCodes->forUserId(1)->willReturn(['en_US', 'fr_FR']);
        $context->buildViolation($constraint->message, ['{{ locale_code }}' => 'de_DE'])
            ->shouldBeCalledOnce()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation($constraint->message, ['{{ locale_code }}' => 'en_GB'])
            ->shouldBeCalledOnce()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($command, new LocaleShouldBeEditableByUser());
    }
}
