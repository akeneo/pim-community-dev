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

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ProductSourceKeys;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ProductSourceKeysValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProductSourceKeysValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ProductSourceKeysValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [new ProductSource([]), new IsNull()]);
    }

    function it_throws_an_exception_if_value_is_not_a_product_source()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new ProductSourceKeys()]);
    }

    function it_validates_a_field_product_source_successfully(ExecutionContextInterface $context)
    {
        $productSource = new ProductSource(['field' => 'test']);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($productSource, new ProductSourceKeys());
    }

    function it_validates_a_text_product_source_successfully(ExecutionContextInterface $context)
    {
        $productSource = new ProductSource(['text' => 'test']);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($productSource, new ProductSourceKeys());
    }

    function it_validates_a_new_line_product_source_successfully(ExecutionContextInterface $context)
    {
        $productSource = new ProductSource(['new_line' => null]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($productSource, new ProductSourceKeys());
    }

    function it_adds_a_violation_when_product_source_does_not_have_required_keys(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ProductSourceKeys();
        $productSource = new ProductSource(['locale' => 'en_US', 'scope' => 'mobile', 'currency' => 'USD']);
        $context->buildViolation($constraint->missingSourceKeyMessage)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($productSource, $constraint);
    }

    function it_adds_a_violation_when_several_required_keys_are_provided(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ProductSourceKeys();

        $productSource = new ProductSource(['field' => 'foo', 'text' => 'bar']);
        $context->buildViolation($constraint->onlyOneSourceKeyExpectedMessage)->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($productSource, $constraint);

        $productSource = new ProductSource(['field' => 'foo', 'new_line' => null]);
        $context->buildViolation($constraint->onlyOneSourceKeyExpectedMessage)->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($productSource, $constraint);

        $productSource = new ProductSource(['text' => 'foo', 'new_line' => null]);
        $context->buildViolation($constraint->onlyOneSourceKeyExpectedMessage)->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($productSource, $constraint);
    }
}
