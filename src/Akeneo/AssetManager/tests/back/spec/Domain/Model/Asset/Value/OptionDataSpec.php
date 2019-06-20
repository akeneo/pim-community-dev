<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use PhpSpec\ObjectBehavior;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class OptionDataSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(OptionData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', ['Hello!']);
        $this->shouldBeAnInstanceOf(OptionData::class);
    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_string()
    {
        $this->beConstructedThrough('createFromNormalize', [['array']]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_can_be_constructed_with_a_zero_numeric_value()
    {
        $this->beConstructedThrough('createFromNormalize', ['0']);
        $this->shouldNotThrow('\InvalidArgumentException')->duringInstantiation();
        $this->shouldBeAnInstanceOf(OptionData::class);
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->beConstructedThrough('createFromNormalize', ['']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_normalizes_itself()
    {

        $this->beConstructedThrough('createFromNormalize', ['my_option_code']);
        $this->normalize()->shouldReturn('my_option_code');
    }
}
