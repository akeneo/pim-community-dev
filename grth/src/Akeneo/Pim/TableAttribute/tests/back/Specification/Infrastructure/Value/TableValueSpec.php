<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;

class TableValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            [
                'nutrition',
                Table::fromNormalized(
                    [
                        ['ingredient' => 'sugar', 'quantity' => 10],
                        ['ingredient' => 'salt', 'quantity' => 20],
                    ]
                ),
                'ecommerce',
                'en_US',
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ValueInterface::class);
        $this->shouldHaveType(TableValue::class);
    }

    function it_does_not_equal_another_value_type()
    {
        $this->isEqual(ScalarValue::scopableLocalizableValue('name', 'test', 'ecommerce', 'en_US'))->shouldBe(false);
    }

    function it_does_not_equal_a_value_with_another_scope_or_locale()
    {
        $this->isEqual(
            TableValue::scopableLocalizableValue(
                'nutrition',
                Table::fromNormalized(
                    [
                        ['ingredient' => 'sugar', 'quantity' => 10],
                        ['ingredient' => 'salt', 'quantity' => 20],
                    ]
                ),
                'ecommerce',
                'fr_FR'
            )
        )->shouldBe(false);
        $this->isEqual(
            TableValue::scopableLocalizableValue(
                'nutrition',
                Table::fromNormalized(
                    [
                        ['ingredient' => 'sugar', 'quantity' => 10],
                        ['ingredient' => 'salt', 'quantity' => 20],
                    ]
                ),
                'mobile',
                'en_US'
            )
        )->shouldBe(false);
        $this->isEqual(
            TableValue::value(
                'nutrition',
                Table::fromNormalized(
                    [
                        ['ingredient' => 'sugar', 'quantity' => 10],
                        ['ingredient' => 'salt', 'quantity' => 20],
                    ]
                )
            )
        )->shouldBe(false);
    }

    function it_does_not_equal_a_table_value_with_updated_data()
    {
        $this->isEqual(
            TableValue::scopableLocalizableValue(
                'nutrition',
                Table::fromNormalized(
                    [
                        ['ingredient' => 'butter', 'quantity' => 20],
                        ['ingredient' => 'salt', 'quantity' => 20],
                    ]
                ),
                'ecommerce',
                'en_US'
            )
        )->shouldBe(false);
    }

    function it_does_not_equal_a_value_if_rows_were_switched()
    {
        $this->isEqual(
            TableValue::scopableLocalizableValue(
                'nutrition',
                Table::fromNormalized(
                    [
                        ['ingredient' => 'salt', 'quantity' => 20],
                        ['ingredient' => 'sugar', 'quantity' => 10],
                    ]
                ),
                'ecommerce',
                'en_US'
            )
        )->shouldBe(false);
    }

    function it_equals_a_similar_value()
    {
        $this->isEqual(
            TableValue::scopableLocalizableValue(
                'nutrition',
                Table::fromNormalized(
                    [
                        ['ingredient' => 'sugar', 'quantity' => 10],
                        ['ingredient' => 'salt', 'quantity' => 20],
                    ]
                ),
                'ecommerce',
                'en_US'
            )
        )->shouldBe(true);

        $this->isEqual(
            TableValue::scopableLocalizableValue(
                'nutrition',
                Table::fromNormalized(
                    [
                        ['quantity' => 10, 'ingredient' => 'sugar'],
                        ['quantity' => 20, 'ingredient' => 'salt'],
                    ]
                ),
                'ecommerce',
                'en_US'
            )
        )->shouldBe(true);
    }
}
