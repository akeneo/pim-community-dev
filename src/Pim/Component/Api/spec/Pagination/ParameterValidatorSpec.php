<?php

    namespace spec\Pim\Component\Api\Pagination;

    use PhpSpec\ObjectBehavior;
    use Pim\Component\Api\Exception\PaginationParametersException;

    class ParameterValidatorSpec extends ObjectBehavior
    {
        function let()
        {
            $this->beConstructedWith(100);
        }

        function it_is_initializable()
        {
            $this->shouldHaveType('Pim\Component\Api\Pagination\ParameterValidator');
        }

        function it_is_a_parameter_validator()
        {
            $this->shouldImplement('Pim\Component\Api\Pagination\ParameterValidatorInterface');
        }

        function it_requires_page_to_be_passed_in_the_paramaters()
        {
            $parameters = [
                'limit' => 10,
            ];

            $this
                ->shouldThrow(new PaginationParametersException('Page number is missing.'))
                ->duringValidate($parameters);
        }

        function it_requires_limit_to_be_passed_in_the_paramaters()
        {
            $parameters = [
                'page' => 1,
            ];

            $this
                ->shouldThrow(new PaginationParametersException('Limit number is missing.'))
                ->duringValidate($parameters);
        }

        function it_validates_integer_values()
        {
            $parameters = [
                'page'  => 1,
                'limit' => 10,
            ];

            $this->validate($parameters);
        }

        function it_validates_integer_as_string_values()
        {
            $parameters = [
                'page'  => '1',
                'limit' => '10',
            ];

            $this->validate($parameters);
        }

        function it_does_not_validates_float_page_values()
        {
            $parameters = [
                'page'  => '1.1',
                'limit' => 10,
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"1.1" is not a valid page number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_string_page_value()
        {
            $parameters = [
                'page'  => 'string',
                'limit' => 10,
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"string" is not a valid page number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_negative_page_number()
        {
            $parameters = [
                'page'  => -5,
                'limit' => 10,
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"-5" is not a valid page number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validates_float_limit_values()
        {
            $parameters = [
                'page'  => 1,
                'limit' => '1.1',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"1.1" is not a valid limit number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_string_limit_value()
        {
            $parameters = [
                'page'  => 1,
                'limit' => 'string',
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"string" is not a valid limit number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_negative_limit_number()
        {
            $parameters = [
                'page'  => 1,
                'limit' => -5,
            ];

            $this
                ->shouldThrow(new PaginationParametersException('"-5" is not a valid limit number.'))
                ->duringValidate($parameters);
        }

        function it_does_not_validate_limit_exceeding_maximum_limit_value()
        {
            $parameters = [
                'page'  => 1,
                'limit' => 200,
            ];

            $this
                ->shouldThrow(new PaginationParametersException('You cannot request more than 100 items.'))
                ->duringValidate($parameters);
        }
    }
