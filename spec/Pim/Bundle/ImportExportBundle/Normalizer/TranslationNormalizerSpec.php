<?php

namespace spec\Pim\Bundle\ImportExportBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;

class TranslationNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_translatable_object_in_xml(
        TranslatableInterface $translatable
    ) {
        $this->supportsNormalization($translatable, 'xml')->shouldReturn(true);
    }

    function it_supports_normalization_of_translatable_object_in_json(
        TranslatableInterface $translatable
    ) {
        $this->supportsNormalization($translatable, 'json')->shouldReturn(true);
    }

    function it_normalizes_translatable_object_using_activated_locales(
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

        $this->normalize($translatable, 'xml', ['locales' => ['en_US', 'de_DE', 'fr_FR', 'fr_BE']])->shouldReturn([
            'label' => [
                'en_US' => 'foo',
                'de_DE' => '',
                'fr_FR' => 'bar',
                'fr_BE' => '',
            ]
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
            'property' => 'description',
            'locales' => ['en_US', 'de_DE', 'fr_FR', 'fr_BE']
        ])->shouldReturn([
            'description' => [
                'en_US' => '',
                'de_DE' => '',
                'fr_FR' => '',
                'fr_BE' => '',
            ]
        ]);
    }
}
