<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCriterion;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidateCriterionSpec extends ObjectBehavior
{
    function it_is_a_validator_of_criterion()
    {
        $this->shouldBeAnInstanceOf(ValidateCriterion::class);
    }

    function it_throws_exception_if_filters_are_empty()
    {
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'searchKey' => []
        ]]);
    }

    function it_throws_an_exception_if_a_filter_does_not_have_operator_key()
    {
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'searchKey' => [['foo' => 'bar']]
        ]]);
    }

    function it_throws_an_exception_if_a_filter_operator_is_not_a_string()
    {
        $this->shouldThrow(InvalidQueryException::class)->during('validate', [[
            'searchKey' => [['operator' => 69]]
        ]]);
    }
}
