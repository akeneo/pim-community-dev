<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_bson_of_attribute_option(AttributeOption $option)
    {
        $this->supportsNormalization($option, 'bson')->shouldBe(true);
        $this->supportsNormalization($option, 'json')->shouldBe(false);
        $this->supportsNormalization($option, 'xml')->shouldBe(false);
    }

    function it_normalizes_attribute_option(AttributeOption $option, AttributeOptionValue $valueUs, AttributeOptionValue $valueFr)
    {
        $option->getCode()->willReturn('red');
        $valueUs->getLocale()->willReturn('en_US');
        $valueUs->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');
        $option->getOptionValues()->willReturn([$valueUs, $valueFr]);

        $this->normalize($option, 'bson', [])->shouldReturn(['code' => 'red', 'en_US' => 'Red', 'fr_FR' => 'Rouge']);
    }
}
