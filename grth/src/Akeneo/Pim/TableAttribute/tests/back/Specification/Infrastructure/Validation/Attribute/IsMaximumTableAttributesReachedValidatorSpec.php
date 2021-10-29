<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\IsMaximumTableAttributesReached;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\IsMaximumTableAttributesReachedValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsMaximumTableAttributesReachedValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $executionContext, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
        $this->initialize($executionContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsMaximumTableAttributesReachedValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_should_throw_an_exception_with_wrong_constraint(
        AttributeInterface $attribute
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$attribute, new Blank()]);
    }

    function it_should_only_validate_attribute(
        ExecutionContext $executionContext
    ) {
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new IsMaximumTableAttributesReached());
    }

    function it_should_only_validate_table_attributes(
        ExecutionContext $executionContext
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::BOOLEAN);

        $executionContext
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, new IsMaximumTableAttributesReached());
    }

    function it_should_not_build_violation_if_table_attribute_is_already_saved(
        AttributeRepositoryInterface $attributeRepository,
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TABLE);
        $attribute->setCode('table_attribute_10');

        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::TABLE)
            ->shouldBeCalled()
            ->willReturn(array_map(
                static fn (int $i): string => sprintf('table_attribute_%d', $i),
                range(1, 50)
            ));

        $executionContext
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, new IsMaximumTableAttributesReached());
    }

    function it_should_build_violation_when_there_are_too_many_table_attributes(
        AttributeRepositoryInterface $attributeRepository,
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TABLE);
        $attribute->setCode('new');

        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::TABLE)
            ->shouldBeCalled()
            ->willReturn(array_map(
                static fn (int $i): string => sprintf('table_attribute_%d', $i),
                range(1, 50)
            ));

        $executionContext
            ->buildViolation(Argument::any(), ['{{ limit }}' => 50])
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder
            ->addViolation()
            ->shouldBeCalled();

        $this->validate($attribute, new IsMaximumTableAttributesReached());
    }

    function it_should_not_build_violation_when_there_are_not_too_many_table_attributes(
        ExecutionContext $executionContext,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TABLE);
        $attribute->setCode('new');

        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::TABLE)
            ->shouldBeCalled()
            ->willReturn(array_map(
                static fn (int $i): string => sprintf('table_attribute_%d', $i),
                range(1, 49)
            ));

        $executionContext
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, new IsMaximumTableAttributesReached());
    }

    function it_should_validate_several_attribute_creations_in_a_row(
        ExecutionContext $executionContext,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::TABLE)
            ->shouldBeCalled()
            ->willReturn(array_map(
                static fn (int $i): string => sprintf('table_attribute_%d', $i),
                range(1, 48)
            ));

        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TABLE);
        $attribute->setCode('table_attribute_3');
        $this->validate($attribute, new IsMaximumTableAttributesReached());

        $attribute1 = new Attribute();
        $attribute1->setType(AttributeTypes::TABLE);
        $attribute1->setCode('new_table_1');
        for ($i = 0; $i < 3; $i++) {
            $this->validate($attribute1, new IsMaximumTableAttributesReached());
        }
        $attribute2 = new Attribute();
        $attribute2->setType(AttributeTypes::TABLE);
        $attribute2->setCode('new_table_2');
        $this->validate($attribute2, new IsMaximumTableAttributesReached());
    }

    function it_should_add_a_violation_when_validate_several_attribute_creations_in_a_row(
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $attributeRepository
            ->getAttributeCodesByType(AttributeTypes::TABLE)
            ->shouldBeCalled()
            ->willReturn(array_map(
                static fn (int $i): string => sprintf('table_attribute_%d', $i),
                range(1, 48)
            ));

        $executionContext
            ->buildViolation(Argument::any(), ['{{ limit }}' => 50])
            ->shouldBeCalledTimes(5)
            ->willReturn($violationBuilder);
        $violationBuilder
            ->addViolation()
            ->shouldBeCalledTimes(5);

        $attribute1 = new Attribute();
        $attribute1->setType(AttributeTypes::TABLE);
        $attribute1->setCode('new_table_1');
        $this->validate($attribute1, new IsMaximumTableAttributesReached());
        $attribute2 = new Attribute();
        $attribute2->setType(AttributeTypes::TABLE);
        $attribute2->setCode('new_table_2');
        $this->validate($attribute2, new IsMaximumTableAttributesReached());

        $attribute3 = new Attribute();
        $attribute3->setType(AttributeTypes::TABLE);
        $attribute3->setCode('new_table_3');
        for ($i = 0; $i < 5; $i++) {
            $this->validate($attribute3, new IsMaximumTableAttributesReached());
        }
    }
}
