<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\ArrayConverter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\ArrayConverter\FlatToStandard;
use Akeneo\Tool\Component\Connector\Exception\ArrayConversionException;
use PhpSpec\ObjectBehavior;

class FlatToStandardSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FlatToStandard::class);
    }

    function it_only_supports_table_fields()
    {
        $this->supportsField(AttributeTypes::TABLE)->shouldBe(true);
        $this->supportsField(AttributeTypes::TEXT)->shouldBe(false);
    }

    function it_converts_json_table_to_standard_format(AttributeInterface $nutrition)
    {
        $nutrition->getCode()->willReturn('nutrition');
        $value = '{"test": "value"}';
        $attributeFieldInfo = ['locale_code' => null, 'scope_code' => null, 'attribute' => $nutrition];
        $this->convert($attributeFieldInfo, $value)->shouldReturn(
            ['nutrition' => [['locale' => null, 'scope' => null, 'data' => ['test' => 'value']]]]
        );
    }

    function it_returns_null_for_empty_string(AttributeInterface $nutrition)
    {
        $nutrition->getCode()->willReturn('nutrition');
        $attributeFieldInfo = ['locale_code' => null, 'scope_code' => null, 'attribute' => $nutrition];
        $this->convert($attributeFieldInfo, '')->shouldReturn(['nutrition' => [['locale' => null, 'scope' => null, 'data' => null]]]
        );
        $this->convert($attributeFieldInfo, ' ')->shouldReturn(['nutrition' => [['locale' => null, 'scope' => null, 'data' => null]]]
        );
    }

    function it_should_throw_an_exception_when_value_is_not_valid_json(AttributeInterface $nutrition)
    {
        $value = '{"value"';
        $this->shouldThrow(ArrayConversionException::class)->during(
            'convert',
            [['locale_code' => null, 'scope_code' => null, 'attribute' => $nutrition], $value]
        );
    }
}
