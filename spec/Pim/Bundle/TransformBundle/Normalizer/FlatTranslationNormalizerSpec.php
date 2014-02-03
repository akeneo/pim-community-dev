<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;

class FlatTranslationNormalizerSpec extends ObjectBehavior
{
    function it_is_a_translation_normalizer()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\TransformBundle\Normalizer\TranslationNormalizer');
    }

    function it_supports_csv_format(TranslatableInterface $translatable)
    {
        $this->supportsNormalization($translatable, 'csv')->shouldReturn(true);
    }

    function it_normalizes_property_by_activated_locale(
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french
    ) {
        $translatable->getTranslations()->willReturn([
            $english, $french
        ]);

        $english->getLocale()->willReturn('en_US');
        $english->getLabel()->willReturn('foo');

        $french->getLocale()->willReturn('fr_FR');
        $french->getLabel()->willReturn('bar');

        $this->normalize($translatable, 'csv', ['locales' => ['en_US', 'de_DE', 'fr_FR', 'fr_BE']])->shouldReturn([
            'label-en_US' => 'foo',
            'label-de_DE' => '',
            'label-fr_FR' => 'bar',
            'label-fr_BE' => '',
        ]);
    }

    function it_ignores_a_locale_if_property_does_not_exist_for_a_translation(
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french
    ) {
        $translatable->getTranslations()->willReturn([
            $english, $french
        ]);

        $this->normalize($translatable, 'xml', [
            'locales'  => ['en_US', 'de_DE', 'fr_FR', 'fr_BE'],
            'property' => 'description'
        ])->shouldReturn([
            'description-en_US' => '',
            'description-de_DE' => '',
            'description-fr_FR' => '',
            'description-fr_BE' => '',
        ]);
    }
}
