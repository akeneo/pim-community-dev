<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\Completeness;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\TransformBundle\Normalizer\TranslationNormalizer;

class CompletenessNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_completeness(Completeness $completeness)
    {
        $this->supportsNormalization($completeness, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($completeness, 'json')->shouldBe(false);
        $this->supportsNormalization($completeness, 'xml')->shouldBe(false);
    }

    function it_normalizes_completeness(
        TranslationNormalizer $normalizer,
        Completeness $completeness,
        Channel $channel,
        Locale $locale
    ) {
        $channel->getCode()->willReturn('ecommerce');
        $locale->getCode()->willReturn('en_US');

        $completeness->getChannel()->willReturn($channel);
        $completeness->getLocale()->willReturn($locale);
        $completeness->getRatio()->willReturn(42);

        $normalizer->normalize($completeness, 'mongodb_json', [])->willReturn(['label' => 'translations']);

        $this->normalize($completeness, 'mongodb_json', [])->shouldReturn([
            "ecommerce-en_US" => 42
        ]);
    }
}
