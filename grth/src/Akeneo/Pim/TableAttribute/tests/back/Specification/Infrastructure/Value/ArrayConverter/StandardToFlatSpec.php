<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\ArrayConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\ArrayConverter\StandardToFlat;
use PhpSpec\ObjectBehavior;

class StandardToFlatSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver, [AttributeTypes::TABLE]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(StandardToFlat::class);
        $this->shouldImplement(ValueConverterInterface::class);
    }

    function it_only_supports_table_attribute_values(AttributeInterface $table, AttributeInterface $name)
    {
        $table->getType()->willReturn(AttributeTypes::TABLE);
        $this->supportsAttribute($table)->shouldBe(true);

        $name->getType()->willReturn(AttributeTypes::TEXT);
        $this->supportsAttribute($name)->shouldBe(false);
    }

    function it_converts_a_standard_table_value_to_flat_format(AttributeColumnsResolver $columnsResolver)
    {
        $data = [
            [
                'locale' => 'en_US',
                'scope' => null,
                'data' => [
                    ['ingredient' => 'salt', 'allergenic' => false],
                    ['ingredient' => 'egg', 'quantity' => 10, 'allergenic' => true],
                ],
            ],
            [
                'locale' => 'fr_FR',
                'scope' => null,
                'data' => [
                    ['ingredient' => 'sugar', 'allergenic' => false],
                    ['ingredient' => 'butter', 'quantity' => 100, 'allergenic' => true],
                ],
            ],
        ];

        $columnsResolver->resolveFlatAttributeName('nutrition', 'en_US', null)
                        ->shouldBeCalled()
                        ->willReturn('nutrition-en_US');
        $columnsResolver->resolveFlatAttributeName('nutrition', 'fr_FR', null)
                        ->shouldBeCalled()
                        ->willReturn('nutrition-fr_FR');

        $this->convert('nutrition', $data)->shouldReturn(
            [
                'nutrition-en_US' => '[{"ingredient":"salt","allergenic":false},{"ingredient":"egg","quantity":10,"allergenic":true}]',
                'nutrition-fr_FR' => '[{"ingredient":"sugar","allergenic":false},{"ingredient":"butter","quantity":100,"allergenic":true}]',
            ]
        );
    }

    function it_converts_null_data_to_empty_string(AttributeColumnsResolver $columnsResolver)
    {
        $data = [
            [
                'locale' => 'en_US',
                'scope' => null,
                'data' => [
                    ['ingredient' => 'salt', 'allergenic' => false],
                    ['ingredient' => 'egg', 'quantity' => 10, 'allergenic' => true],
                ],
            ],
            [
                'locale' => 'fr_FR',
                'scope' => null,
                'data' => null,
            ],
        ];

        $columnsResolver->resolveFlatAttributeName('nutrition', 'en_US', null)
            ->shouldBeCalled()
            ->willReturn('nutrition-en_US');
        $columnsResolver->resolveFlatAttributeName('nutrition', 'fr_FR', null)
            ->shouldBeCalled()
            ->willReturn('nutrition-fr_FR');

        $this->convert('nutrition', $data)->shouldReturn(
            [
                'nutrition-en_US' => '[{"ingredient":"salt","allergenic":false},{"ingredient":"egg","quantity":10,"allergenic":true}]',
                'nutrition-fr_FR' => '',
            ]
        );
    }
}
