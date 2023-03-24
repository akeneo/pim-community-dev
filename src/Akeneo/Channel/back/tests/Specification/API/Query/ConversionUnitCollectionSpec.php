<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\API\Query;

use Akeneo\Channel\API\Query\ConversionUnitCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\InvalidArgumentException;

class ConversionUnitCollectionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromArray', [['an_measurement_attribute' => 'GRAM', 'another_measurement_attribute' => 'POUND']]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConversionUnitCollection::class);
    }

    public function it_throws_exception_when_trying_to_create_it_with_non_string_array(): void
    {
        $this->shouldThrow(InvalidArgumentException::class)->during('fromArray', [['an_attribute' => 1]]);
    }

    public function it_says_if_it_has_a_conversion_unit_or_not(): void
    {
        $this->hasConversionUnit('an_measurement_attribute')->shouldReturn(true);
        $this->hasConversionUnit('another_measurement_attribute')->shouldReturn(true);
        $this->hasConversionUnit('an_unknown_measurement_attribute')->shouldReturn(false);
    }

    public function it_returns_a_conversion_unit(): void
    {
        $this->getConversionUnit('an_measurement_attribute')->shouldReturn('GRAM');
        $this->getConversionUnit('another_measurement_attribute')->shouldReturn('POUND');
        $this->getConversionUnit('an_unknown_measurement_attribute')->shouldReturn(null);
    }
}
