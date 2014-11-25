<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;

class TranslationNormalizerSpec extends ObjectBehavior
{
    function it_supports_csv_format(TranslatableInterface $translatable)
    {
        $this->supportsNormalization($translatable, 'csv')->shouldReturn(true);
    }

    function it_normalizes_property_by_activated_locale(
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        AttributeTranslation $german
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

        $this->normalize($translatable, 'csv', [
            'locales'  => ['en_US', 'de_DE', 'fr_FR', 'fr_BE'],
            'property' => 'label'
        ])->shouldReturn([
            'label-en_US' => '',
            'label-de_DE' => '',
            'label-fr_FR' => '',
            'label-fr_BE' => '',
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

        $this->normalize($translatable, 'csv', [
            'locales'  => ['en_US', 'fr_FR'],
            'property' => 'label'
        ])->shouldReturn([
            'label-en_US' => '',
            'label-fr_FR' => '',
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

        $this->normalize($translatable, 'csv', [
            'locales'  => [],
            'property' => 'label'
        ])->shouldReturn([
            'label-en_US' => 'foo',
            'label-fr_FR' => 'bar',
            'label-de_DE' => 'baz',
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
