<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\MongoDB\Normalizer\ProductValueNormalizer;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_attribute_option(AttributeOption $option)
    {
        $this->supportsNormalization($option, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($option, 'json')->shouldBe(false);
        $this->supportsNormalization($option, 'xml')->shouldBe(false);
    }

    function it_normalizes_attribute_option(AttributeOption $option, AttributeOptionValue $valueUs, AttributeOptionValue $valueFr)
    {
        $option->getId()->willReturn(42);
        $option->getCode()->willReturn('red');
        $valueUs->getLocale()->willReturn('en_US');
        $valueUs->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');
        $option->getOptionValues()->willReturn([$valueUs, $valueFr]);

        $this->normalize($option, 'mongodb_json', [])->shouldReturn(
            [
                ProductValueNormalizer::NORM_ITEM_KEY => 42,
                ProductValueNormalizer::NORM_ITEM_VALUE => [
                    'id' => 42,
                    'code' => 'red',
                    'optionValues' => [
                        'en_US' => ['value' => 'Red', 'locale' => 'en_US'],
                        'fr_FR' => ['value' => 'Rouge', 'locale' => 'fr_FR']
                    ]
                ]
            ]
        );
    }
}
