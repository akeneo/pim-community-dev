<?php

    namespace spec\Pim\Component\Api\Pagination;

    use PhpSpec\ObjectBehavior;
    use Pim\Component\Api\Exception\PaginationParametersException;

    class ParameterValidatorSpec extends ObjectBehavior
    {
        function let()
        {
            $this->beConstructedWith(['pagination' => ['limit_max' => 100]]);
        }

        function it_is_initializable()
        {
            $this->shouldHaveType('Pim\Component\Api\Pagination\ParameterValidator');
        }

        function it_is_a_parameter_validator()
        {
            $this->shouldImplement('Pim\Component\Api\Pagination\ParameterValidatorInterface');
        }

        function it_validates_offset_pagination_by_default()
        {
            $parameters = [
                'page'  => '1.1',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"1.1" is not a valid page number.'))
                ->duringValidate($parameters);
        }

        function it_validates_limit_with_search_after_pagination()
        {
            $parameters = [
                'limit'  => '1.1',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"1.1" is not a valid limit number.'))
                ->duringValidate($parameters);
        }

        function it_validates_integer_values_with_offset_pagination()
        {
            $parameters = [
                'page'            => 1,
                'limit'           => 10,
                'pagination_type' => 'page',
            ];

            $this->validate($parameters);
        }

        function it_validates_integer_as_string_values_with_offset_pagination()
        {
            $parameters = [
                'page'            => '1',
                'limit'           => '10',
                'pagination_type' => 'page',
            ];

            $this->validate($parameters);
        }

        function it_does_not_validates_float_page_values_with_offset_pagination()
        {
            $parameters = [
                'page'            => '1.1',
                'pagination_type' => 'page',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"1.1" is not a valid page number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_string_page_value_with_offset_pagination()
        {
            $parameters = [
                'page'            => 'string',
                'pagination_type' => 'page',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"string" is not a valid page number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_negative_page_number_with_offset_pagination()
        {
            $parameters = [
                'page'            => -5,
                'pagination_type' => 'page',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"-5" is not a valid page number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validates_float_limit_values_with_offset_pagination()
        {
            $parameters = [
                'limit'           => '1.1',
                'pagination_type' => 'page',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"1.1" is not a valid limit number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_string_limit_value_with_offset_pagination()
        {
            $parameters = [
                'limit'           => 'string',
                'pagination_type' => 'page',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"string" is not a valid limit number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_negative_limit_number_with_offset_pagination()
        {
            $parameters = [
                'limit'           => -5,
                'pagination_type' => 'page',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"-5" is not a valid limit number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_limit_exceeding_maximum_limit_value_with_offset_pagination()
        {
            $parameters = [
                'limit'           => 200,
                'pagination_type' => 'page',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('You cannot request more than 100 items.'))
                ->duringValidate($parameters);
        }

        function it_throws_an_exception_when_unknown_pagination_type()
        {
            $this
                ->shouldThrow(new PaginationParametersException('Pagination type does not exist.'))
                ->duringValidate(['pagination_type' => 'unknown']);
        }

        function it_throws_an_exception_when_search_after_pagination_not_supported()
        {
            $this ->shouldThrow(new PaginationParametersException('Pagination type does not exist.'))
                ->duringValidate(['pagination_type' => 'unknown']);

            $this ->shouldThrow(new PaginationParametersException('Pagination type does not exist.'))
                ->duringValidate(['pagination_type' => 'unknown'], ['support_search_after' => false]);
        }
    }
