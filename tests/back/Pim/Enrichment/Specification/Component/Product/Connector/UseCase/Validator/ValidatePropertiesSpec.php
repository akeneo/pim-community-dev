<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ValidatePropertiesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($attributeRepository);
    }

    function it_does_not_throw_exception_if_it_filters_on_field()
    {
        $this->shouldNotThrow(InvalidQueryException::class)->during('validate', [[
            'family' => [['operator' => Operators::IN_LIST]]
        ]]);
    }

    function it_does_not_throw_exception_if_attribute_exists(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('color')->willReturn($attribute)->shouldBeCalled();
        $this->shouldNotThrow(InvalidQueryException::class)->during('validate', [[
            'color' => [['operator' => Operators::IN_LIST]]
        ]]);
    }

    function it_does_not_throw_exception_if_attribute_code_is_numeric(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('1000')->willReturn($attribute)->shouldBeCalled();
        $this->shouldNotThrow(InvalidQueryException::class)->during(
            'validate',
            [
                [
                    1000 => [['operator' => Operators::IN_LIST]]
                ]
            ]
        );
    }

    function it_throws_exception_if_filter_is_not_a_field_neither_an_attribute(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('foo')->willReturn(null)->shouldBeCalled();
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'foo' => [['operator' => Operators::IN_LIST]]
        ]]);
    }
}
