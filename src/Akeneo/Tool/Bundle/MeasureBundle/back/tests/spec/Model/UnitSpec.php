<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PhpSpec\ObjectBehavior;

class UnitSpec extends ObjectBehavior
{
    private const UNIT_CODE = 'meter';
    private const UNIT_LABELS = ['fr_FR' => 'metre', 'en_US' => 'meter'];
    private const SYMBOL = 'm';
    private const OPERATION_OPERATOR = 'mul';
    private const OPERATION_VALUE = '150';

    public function let()
    {
        $this->beConstructedThrough(
            'create',
            [
                UnitCode::fromString(self::UNIT_CODE),
                LabelCollection::fromArray(self::UNIT_LABELS),
                [Operation::create(self::OPERATION_OPERATOR, self::OPERATION_VALUE)],
                self::SYMBOL
            ]
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
                'code'                  => self::UNIT_CODE,
                'labels'                => self::UNIT_LABELS,
                'convert_from_standard' => [
                    ['operator' => self::OPERATION_OPERATOR, 'value' => self::OPERATION_VALUE]
                ],
                'symbol'                => self::SYMBOL,
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
                    LabelCollection::fromArray(self::UNIT_LABELS),
                    [$wrongOperation],
                    self::SYMBOL
                ]
            );
    }

    function it_should_be_created_with_at_least_one_operation()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'create',
                [
                    UnitCode::fromString(self::UNIT_CODE),
                    LabelCollection::fromArray(self::UNIT_LABELS),
                    [],
                    self::SYMBOL
                ]
            );
    }
        function it_returns_the_label_of_the_provided_locale()
    {
        $this->getLabel(LocaleIdentifier::fromCode('fr_FR'))->shouldReturn('metre');
    }

    function it_returns_the_code_between_brackets_when_there_is_no_label_for_the_locale()
    {
        $this->getLabel(LocaleIdentifier::fromCode('UNKNOWN'))->shouldReturn('[meter]');
    }

    function it_tells_if_it_is_a_standard_unit()
    {
        $this->beConstructedThrough(
            'create',
            [
                UnitCode::fromString(self::UNIT_CODE),
                LabelCollection::fromArray(self::UNIT_LABELS),
                [Operation::create('mul', '1')],
                self::SYMBOL
            ]
        );
    }

    function it_tells_if_it_is_not_be_a_standard_unit()
    {
        $this->canBeAStandardUnit()->shouldReturn(false);
    }
}
