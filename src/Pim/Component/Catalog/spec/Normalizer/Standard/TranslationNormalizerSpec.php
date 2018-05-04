<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;

class TranslationNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(TranslatableInterface $translatable)
    {
        $this->supportsNormalization($translatable, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($translatable, 'xml')->shouldReturn(false);
        $this->supportsNormalization($translatable, 'json')->shouldReturn(false);
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

        $this->normalize($translatable, 'standard', ['locales' => ['en_US', 'de_DE', 'fr_FR', 'fr_BE']])->shouldReturn(
            [
                'en_US' => 'foo',
                'de_DE' => null,
                'fr_FR' => 'bar',
                'fr_BE' => null,
            ]
        );
    }

    function it_ignores_a_locale_if_property_does_not_exist_for_a_translation(
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french
    ) {
        $translatable->getTranslations()->willReturn([
            $english, $french
        ]);

        $this->normalize($translatable, 'standard', [
            'property' => 'label',
            'locales' => ['en_US', 'de_DE', 'fr_FR', 'fr_BE']
        ])->shouldReturn(
            [
                'en_US' => null,
                'de_DE' => null,
                'fr_FR' => null,
                'fr_BE' => null,
            ]
        );
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

        $this->normalize($translatable, 'standard', [
            'locales'  => ['en_US', 'fr_FR'],
            'property' => 'label'
        ])->shouldReturn(
            [
                'en_US' => null,
                'fr_FR' => null,
            ]
        );
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

        $this->normalize($translatable, 'standard', [
            'locales'  => [],
            'property' => 'label'
        ])->shouldReturn(
            [
                'en_US' => 'foo',
                'fr_FR' => 'bar',
                'de_DE' => 'baz',
            ]
        );
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

        $this->shouldThrow('\LogicException')->duringNormalize($translatable, 'standard', [
            'locales'  => [],
            'property' => 'unknown_property'
        ]);
    }
}
