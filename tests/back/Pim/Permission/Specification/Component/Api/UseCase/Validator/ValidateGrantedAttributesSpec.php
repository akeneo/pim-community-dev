<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Api\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ValidateGrantedAttributesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($attributeRepository, $authorizationChecker);
    }

    function it_does_nothing_without_attribute(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $this->validate(null);
    }

    function it_throws_exception_if_attribute_is_not_granted(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup
    ) {
        $attributeRepository->findOneByIdentifier('foo')->shouldBeCalled()->willReturn($attribute);
        $attribute->getGroup()->shouldBeCalled()->willReturn($attributeGroup);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup)->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(InvalidQueryException::class)->during('validate', [['foo']]);
    }

    function it_does_not_throws_exception_if_attribute_is_granted(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup
    ) {
        $attributeRepository->findOneByIdentifier('foo')->shouldBeCalled()->willReturn($attribute);
        $attribute->getGroup()->shouldBeCalled()->willReturn($attributeGroup);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup)->shouldBeCalled()->willReturn(true);

        $this->shouldNotThrow(InvalidQueryException::class)->during('validate', [['foo']]);
    }
}
