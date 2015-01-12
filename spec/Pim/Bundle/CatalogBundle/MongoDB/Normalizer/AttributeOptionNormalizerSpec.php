<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionValueInterface;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_attribute_option(AttributeOptionInterface $option)
    {
        $this->supportsNormalization($option, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($option, 'json')->shouldBe(false);
        $this->supportsNormalization($option, 'xml')->shouldBe(false);
    }

    function it_normalizes_attribute_option(
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $valueUs,
        AttributeOptionValueInterface $valueFr
    ) {
        $option->getId()->willReturn(42);
        $option->getCode()->willReturn('red');
        $valueUs->getLocale()->willReturn('en_US');
        $valueUs->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');
        $option->getOptionValues()->willReturn([$valueUs, $valueFr]);

        $this->normalize($option, 'mongodb_json', [])->shouldReturn(
            [
                'id' => 42,
                'code' => 'red',
                'optionValues' => [
                    'en_US' => ['value' => 'Red', 'locale' => 'en_US'],
                    'fr_FR' => ['value' => 'Rouge', 'locale' => 'fr_FR']
                ]
            ]
        );
    }
}
