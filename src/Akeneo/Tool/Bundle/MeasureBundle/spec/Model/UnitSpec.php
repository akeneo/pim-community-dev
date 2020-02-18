<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PhpSpec\ObjectBehavior;

class UnitSpec extends ObjectBehavior
{
    private const UNIT_CODE = 'meter';
    private const SYMBOL = 'm';
    private const OPERATION_OPERATOR = 'mul';
    private const OPERATION_VALUE = '150';

    public function let()
    {
        $this->beConstructedThrough(
            'create',
            [UnitCode::fromString(self::UNIT_CODE), [Operation::create(self::OPERATION_OPERATOR, self::OPERATION_VALUE)], self::SYMBOL]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Unit::class);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn(
            [
                'code' => self::UNIT_CODE,
                'convert_from_standard' => [
                    ['operator' => self::OPERATION_OPERATOR, 'value' => self::OPERATION_VALUE]
                ],
                'symbol' => self::SYMBOL,
            ]
        );
    }

    function it_cannot_created_with_something_else_than_a_list_of_operations()
    {
        $wrongOperation = 1234;
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'create',
                [
                    UnitCode::fromString(self::UNIT_CODE),
                    [$wrongOperation],
                    'm'
                ]
            );
    }
}
