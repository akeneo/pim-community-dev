<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginationParametersValidator;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use PhpSpec\ObjectBehavior;

class ValidatePaginationSpec extends ObjectBehavior
{
    function let(PaginationParametersValidator $paginationParametersValidator) {
        $this->beConstructedWith($paginationParametersValidator);
    }

    function it_adapts_query(PaginationParametersValidator $paginationParametersValidator) {
        $paginationParametersValidator->validate([
            'pagination_type' => PaginationTypes::OFFSET,
            'limit' => 42,
            'page' => 69,
            'with_count' => true,
        ], [
            'support_search_after' => true
        ])->shouldBeCalled();

        $this->validate(PaginationTypes::OFFSET, 69, 42, 'true');
    }

    function it_throws_exception_when_pagination_parameters_are_invalid(PaginationParametersValidator $paginationParametersValidator)
    {
        $paginationParametersValidator->validate([
            'pagination_type' => PaginationTypes::OFFSET,
            'limit' => 42,
            'page' => 69,
            'with_count' => true,
        ], [
            'support_search_after' => true
        ])->willThrow(PaginationParametersException::class);

        $this->shouldThrow(InvalidQueryException::class)->during('validate', [PaginationTypes::OFFSET, 69, 42, 'true']);
    }
}
