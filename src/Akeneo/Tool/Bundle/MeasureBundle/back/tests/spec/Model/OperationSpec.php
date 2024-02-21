<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use PhpSpec\ObjectBehavior;

class OperationSpec extends ObjectBehavior
{
    private const VALUE = '150';
    private const OPERATOR = 'mul';

    function let()
    {
        $this->beConstructedThrough('create', [self::OPERATOR, self::VALUE]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Operation::class);
    }

    function it_should_be_normalizable()
    {
        $this->normalize()->shouldReturn(
            ['operator' => self::OPERATOR, 'value' => self::VALUE]
        );
    }

    function it_cannot_be_constructed_with_an_unsupported_operator()
    {
        $invalidOperation = 'invalid_operation';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$invalidOperation, self::VALUE]);
    }

    function it_cannot_be_constructed_with_a_non_numeric_string_value()
    {
        $invalidValue = 'not a numeric_value';
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [self::OPERATOR, $invalidValue]);

    }

    function it_cannot_be_constructed_with_scientific_notation()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [self::OPERATOR, '7E-10']);
    }
}
