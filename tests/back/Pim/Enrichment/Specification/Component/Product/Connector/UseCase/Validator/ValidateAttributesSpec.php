<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidateAttributesSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository) {
        $this->beConstructedWith($attributeRepository);
    }

    public function it_does_not_validate_attributes_if_attributes_are_not_provided(QueryParametersCheckerInterface $queryParametersChecker)
    {
        $queryParametersChecker->checkAttributesParameters(Argument::cetera())->shouldNotBeCalled();
        $this->validate(null);
    }

    public function it_raises_an_exception_if_an_attribute_does_not_exist(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $attributeBuilder = new Builder();
        $color = $attributeBuilder->withCode('color')->build();

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $attributeRepository->findOneByIdentifier('name')->willReturn(null);

        $this->shouldThrow(new InvalidQueryException('Attribute "name" does not exist.'))
            ->during('validate', [['color', 'name']]);
    }

    public function it_raises_an_exception_if_several_attributes_do_not_exist(
        IdentifiableObjectRepositoryInterface$attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('color')->willReturn(null);
        $attributeRepository->findOneByIdentifier('name')->willReturn(null);

        $this->shouldThrow(new InvalidQueryException('Attributes "color, name" do not exist.'))
            ->during('validate', [['color', 'name']]);
    }

    function it_does_not_raise_an_exception_if_attribute_exist(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $attributeBuilder = new Builder();
        $color = $attributeBuilder->withCode('color')->build();
        $name = $attributeBuilder->withCode('name')->build();

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $this->shouldNotThrow('UnprocessableEntityHttpException')
            ->during('validate', [['color', 'name']]);
    }
}
