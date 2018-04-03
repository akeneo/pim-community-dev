<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\Constraints\ScopableValue;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ScopableValueValidatorSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $channelRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($channelRepository);
        $this->initialize($context);
    }

    function it_does_not_validate_if_object_is_not_a_product_value(
        $context,
        ScopableValue $constraint
    ) {
        $object = new \stdClass();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_does_not_add_violations_if_value_is_scopable_and_has_an_existing_scope(
        $context,
        $channelRepository,
        ValueInterface $value,
        AttributeInterface $scopableAttribute,
        ChannelInterface $existingChannel,
        ScopableValue $constraint
    ) {
        $value->getAttribute()->willReturn($scopableAttribute);
        $scopableAttribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn('mobile');
        $channelRepository->findOneByIdentifier('mobile')->willReturn($existingChannel);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_adds_violations_if_value_is_scopable_and_does_not_have_scope(
        $context,
        ValueInterface $value,
        AttributeInterface $scopableAttribute,
        ScopableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->getAttribute()->willReturn($scopableAttribute);
        $scopableAttribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn(null);
        $scopableAttribute->getCode()->willReturn('attributeCode');

        $violationData = [
            '%attribute%' => 'attributeCode'
        ];
        $context->buildViolation($constraint->expectedScopeMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }

    function it_adds_violations_if_value_is_not_scopable_and_a_scope_is_provided(
        $context,
        ValueInterface $value,
        AttributeInterface $notScopableAttribute,
        ScopableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->getAttribute()->willReturn($notScopableAttribute);
        $notScopableAttribute->isScopable()->willReturn(false);
        $value->getScope()->willReturn('aScope');
        $notScopableAttribute->getCode()->willReturn('attributeCode');

        $violationData = [
            '%attribute%' => 'attributeCode'
        ];
        $context->buildViolation($constraint->unexpectedScopeMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }

    function it_adds_violations_if_value_is_scopable_and_its_scope_does_not_exist(
        $context,
        $channelRepository,
        ValueInterface $value,
        AttributeInterface $scopableAttribute,
        ScopableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->getAttribute()->willReturn($scopableAttribute);
        $scopableAttribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn('inexistingChannel');
        $scopableAttribute->getCode()->willReturn('attributeCode');
        $channelRepository->findOneByIdentifier('inexistingChannel')->willReturn(null);

        $violationData = [
            '%attribute%' => 'attributeCode',
            '%channel%'   => 'inexistingChannel'
        ];
        $context->buildViolation($constraint->inexistingScopeMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }
}
