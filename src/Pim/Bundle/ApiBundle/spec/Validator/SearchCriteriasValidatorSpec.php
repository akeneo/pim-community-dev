<?php

namespace spec\Pim\Bundle\ApiBundle\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\ApiBundle\Validator\SearchCriteriasValidator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SearchCriteriasValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SearchCriteriasValidator::class);
    }

    function it_should_throw_an_exception_if_json_is_null()
    {
        $this->shouldThrow(new BadRequestHttpException('Search query parameter should be valid JSON.'))
            ->during('validate', ['']);
    }

    function it_should_throw_an_exception_if_it_is_not_an_array()
    {
        $this->shouldThrow(
            new UnprocessableEntityHttpException('Search query parameter has to be an array, "string" given.')
        )
        ->during('validate', ['"string"']);
    }

    function it_should_throw_an_exception_if_it_is_not_correctly_structured()
    {
        $this->shouldThrow(
            new UnprocessableEntityHttpException('Structure of filter "categories" should respect this structure: {"categories":[{"operator": "my_operator", "value": "my_value"}]}.')
        )
        ->during('validate', ['{"categories":[]}']);
    }

    function it_should_throw_an_exception_if_operator_is_missing()
    {
        $this->shouldThrow(
            new UnprocessableEntityHttpException('Operator is missing for the property "categories".')
        )
        ->during('validate', ['{"categories":[{"value": "my_value"}]}']);
    }

    function it_should_throw_an_exception_if_value_is_missing()
    {
        $this->shouldThrow(
            new UnprocessableEntityHttpException('Value is missing for the property "categories".')
        )
        ->during('validate', ['{"categories":[{"operator": "my_operator"}]}']);
    }
}
