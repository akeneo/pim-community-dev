<?php

namespace Specification\Akeneo\Pim\Permission\Component\Validator\Constraint;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Validator\Constraint\AreGrantedAttributes;
use Akeneo\Pim\Permission\Component\Validator\Constraint\AreGrantedAttributesValidator;
use Akeneo\Test\Common\Structure\Attribute;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AreGrantedAttributesValidatorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($attributeRepository, $authorizationChecker);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AreGrantedAttributesValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_is_not_valid_when_attributes_are_not_granted(
        $context,
        $attributeRepository,
        $authorizationChecker,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attributeBuilder = new Attribute\Builder();
        $fooAttribute = $attributeBuilder->withCode('foo');

        $attributeBuilder = new Attribute\Builder();
        $bazAttribute = $attributeBuilder->withCode('baz');

        $attributeBuilder = new Attribute\Builder();
        $barAttribute = $attributeBuilder->withCode('bar');

        $attributeRepository->findOneByIdentifier('foo')->willReturn($fooAttribute);
        $attributeRepository->findOneByIdentifier('baz')->willReturn($bazAttribute);
        $attributeRepository->findOneByIdentifier('bar')->willReturn($barAttribute);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $fooAttribute)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $bazAttribute)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $barAttribute)->willReturn(true);

        $constraint = new AreGrantedAttributes();
        $context
            ->buildViolation('Attributes "%attributes%" are not granted.', ['%attributes%' => 'foo,baz'])
            ->willReturn($violation);

        $violation->addViolation()->shouldBeCalled();

        $this->validate(['foo', 'baz', 'bar'], $constraint);
    }

    function it_is_valid_when_attributes_are_granted(
        $context,
        $attributeRepository,
        $authorizationChecker
    ) {
        $attributeBuilder = new Attribute\Builder();
        $fooAttribute = $attributeBuilder->withCode('foo');

        $attributeBuilder = new Attribute\Builder();
        $bazAttribute = $attributeBuilder->withCode('baz');

        $attributeBuilder = new Attribute\Builder();
        $barAttribute = $attributeBuilder->withCode('bar');

        $attributeRepository->findOneByIdentifier('foo')->willReturn($fooAttribute);
        $attributeRepository->findOneByIdentifier('baz')->willReturn($bazAttribute);
        $attributeRepository->findOneByIdentifier('bar')->willReturn($barAttribute);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $fooAttribute)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $bazAttribute)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $barAttribute)->willReturn(true);

        $constraint = new AreGrantedAttributes();
        $context
            ->buildViolation()
            ->shouldNotBeCalled();

        $this->validate(['foo', 'baz', 'bar'], $constraint);
    }
}
