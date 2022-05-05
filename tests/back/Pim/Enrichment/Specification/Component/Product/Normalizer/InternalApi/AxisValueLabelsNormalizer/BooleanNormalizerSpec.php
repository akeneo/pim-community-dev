<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\BooleanTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

class BooleanNormalizerSpec extends ObjectBehavior
{
    function let(
        BooleanTranslator $booleanTranslator
    ) {
        $this->beConstructedWith($booleanTranslator);
    }

    function it_is_an_axis_value_labels_normalizer()
    {
        $this->shouldImplement(AxisValueLabelsNormalizer::class);
    }

    function it_only_normalizes_boolean_values()
    {
        $this->supports(AttributeTypes::BOOLEAN)->shouldReturn(true);
        $this->supports(AttributeTypes::OPTION_SIMPLE_SELECT)->shouldReturn(false);
        $this->supports('foobar')->shouldReturn(false);
    }

    function it_normalizes_a_true_value(
        BooleanTranslator $booleanTranslator
    ) {
        $booleanTranslator->translate('', [], ['1'], 'en_US')->shouldBeCalled()->willReturn(['Yes']);
        $this->normalize(ScalarValue::value('my_boolean_attribute', true), 'en_US')->shouldReturn('Yes');
    }

    function it_normalizes_a_false_value(
        BooleanTranslator $booleanTranslator
    ) {
        $booleanTranslator->translate('', [], ['0'], 'en_US')->shouldBeCalled()->willReturn(['No']);
        $this->normalize(ScalarValue::value('my_boolean_attribute', false), 'en_US')->shouldReturn('No');
    }
}
