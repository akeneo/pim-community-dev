<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetTableValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'nutrition',
            'ecommerce',
            'en_US',
            [
                ['ingredient' => 'salt'],
                ['ingredient' => 'egg', 'quantity' => 2],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetTableValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('nutrition');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_table_value()
    {
        $this->tableValue()->shouldBeLike(
            [
                ['ingredient' => 'salt'],
                ['ingredient' => 'egg', 'quantity' => 2],
            ]
        );
    }

    function it_must_be_instantiated_with_valid_data_structure()
    {
        $this->beConstructedWith('nutrition',
            'ecommerce',
            'en_US',
            ['ingredient' => 'salt']
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith('nutrition',
            'ecommerce',
            'en_US',
            [
                'wrong_index_1' => ['ingredient' => 'salt'],
                'wrong_index_2' => ['ingredient' => 'egg', 'quantity' => 2],
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_instantiated_with_integer_column_codes()
    {
        $this->beConstructedWith('nutrition',
            'ecommerce',
            'en_US',
            [
                ['ingredient' => 'egg', 42 => 'michel', 420 => 12],
            ]
        );

        $this->tableValue()->shouldBeLike(
            [
                ['ingredient' => 'egg', 42 => 'michel', 420 => 12],
            ]
        );
    }
}
