<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;

class TranslationNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
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
            'labels' => [
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
            'property' => 'label',
            'locales' => ['en_US', 'de_DE', 'fr_FR', 'fr_BE']
        ])->shouldReturn([
            'labels' => [
                'en_US' => '',
                'de_DE' => '',
                'fr_FR' => '',
                'fr_BE' => '',
            ]
        ]);
    }

    function it_ignores_a_locale_not_provided_in_context(
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        AttributeTranslation $german
    ) {
        $translatable->getTranslations()->willReturn([
            $english, $french, $german
        ]);

        $this->normalize($translatable, 'xml', [
            'locales'  => ['en_US', 'fr_FR'],
            'property' => 'label'
        ])->shouldReturn([
            'labels' => ['en_US' => '', 'fr_FR' => ''],
        ]);
    }

    function it_provides_all_locales_if_no_list_provided_in_context(
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        AttributeTranslation $german
    ) {
        $translatable->getTranslations()->willReturn([
            $english, $french, $german
        ]);

        $english->getLocale()->willReturn('en_US');
        $english->getLabel()->willReturn('foo');

        $french->getLocale()->willReturn('fr_FR');
        $french->getLabel()->willReturn('bar');

        $german->getLocale()->willReturn('de_DE');
        $german->getLabel()->willReturn('baz');

        $this->normalize($translatable, 'xml', [
            'locales'  => [],
            'property' => 'label'
        ])->shouldReturn([
            'labels' => ['en_US' => 'foo', 'fr_FR' => 'bar', 'de_DE' => 'baz'],
        ]);
    }

    function it_throws_an_exception_if_method_not_exists(
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        AttributeTranslation $german
    ) {
        $translatable->getTranslations()->willReturn([
            $english, $french, $german
        ]);

        $english->getLocale()->willReturn('en_US');
        $english->getLabel()->willReturn('foo');

        $french->getLocale()->willReturn('fr_FR');
        $french->getLabel()->willReturn('bar');

        $german->getLocale()->willReturn('de_DE');
        $german->getLabel()->willReturn('baz');

        $this->shouldThrow('\LogicException')->duringNormalize($translatable, 'csv', [
            'locales'  => [],
            'property' => 'unknown_property'
        ]);
    }
}
