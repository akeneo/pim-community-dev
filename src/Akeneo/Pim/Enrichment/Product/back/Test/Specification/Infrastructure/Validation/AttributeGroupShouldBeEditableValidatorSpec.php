<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\AttributeGroupShouldBeEditable;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\AttributeGroupShouldBeEditableValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeGroupShouldBeEditableValidatorSpec extends ObjectBehavior
{
    function let(IsAttributeEditable $isAttributeEditable, ExecutionContext $executionContext)
    {
        $this->beConstructedWith($isAttributeEditable);
        $this->initialize($executionContext);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(AttributeGroupShouldBeEditableValidator::class);
    }

    function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            new SetTextValue('identifier1', null, null, 'foo'),
            new NotBlank(),
        ]);
    }

    function it_can_only_validate_value_user_intents(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            new \stdClass(),
            new AttributeGroupShouldBeEditable(),
        ]);
    }

    function it_should_build_a_violation_when_the_attribute_value_is_not_editable(
        IsAttributeEditable $isAttributeEditable,
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ): void {
        $isAttributeEditable->forCode('attributeCode', 1)->willReturn(false);

        $executionContext->getRoot()->shouldBeCalledOnce()->willReturn(UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []));

        $executionContext->buildViolation(
            'pim_enrich.product.validation.upsert.attribute_group_no_access_to_attributes',
            [ '{{ attributeCode }}' => 'attributeCode']
        )->shouldBeCalledOnce()->willReturn(
            $constraintViolationBuilder
        );

        $constraintViolationBuilder->setCode('5')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(
            new SetTextValue('attributeCode', null, null, 'foo'),
            new AttributeGroupShouldBeEditable()
        );
    }

    function it_should_not_build_any_violation_when_the_attribute_value_is_editable(
        IsAttributeEditable $isAttributeEditable,
        ExecutionContext $executionContext,
    ): void {
        $isAttributeEditable->forCode('attributeCode', 1)->willReturn(true);

        $executionContext->getRoot()->shouldBeCalledOnce()->willReturn(UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []));

        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(
            new SetTextValue('attributeCode', null, null, 'foo'),
            new AttributeGroupShouldBeEditable()
        );
    }
}
