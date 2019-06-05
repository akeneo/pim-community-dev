<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Api\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ValidateGrantedPropertiesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($attributeRepository, $authorizationChecker);
    }

    function it_does_not_throw_exception_when_filtering_on_granted_attributes(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $color = new Attribute();
        $name = new Attribute();

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $color)->willReturn(true);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $name)->willReturn(true);

        $this->shouldNotThrow(InvalidQueryException::class)->during('validate', [
            [
                'name' => [['operator' => 'EQUALS']],
                'color' => [['operator' => 'EQUALS']],
            ]
        ]);
    }

    function it_throws_an_exception_when_filtering_on_ungranted_attributes(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $color = new Attribute();
        $name = new Attribute();

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $color)->willReturn(true);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $name)->willReturn(false);

        $this->shouldThrow(InvalidQueryException::class)->during('validate', [
            [
                'name' => [['operator' => 'EQUALS']],
                'color' => [['operator' => 'EQUALS']],
            ]
        ]);
    }

    function it_does_not_validate_field_on_product_filters()
    {
        $this->shouldNotThrow(InvalidQueryException::class)->during('validate', [
            [
                'completeness' => []
            ]
        ]);
    }
}
