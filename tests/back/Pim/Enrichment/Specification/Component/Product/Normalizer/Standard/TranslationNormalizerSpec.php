<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PhpSpec\ObjectBehavior;

class TranslationNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TranslationNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_normalization(TranslatableInterface $translatable)
    {
        $this->supportsNormalization($translatable, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($translatable, 'xml')->shouldReturn(false);
        $this->supportsNormalization($translatable, 'json')->shouldReturn(false);
    }

    function it_normalizes_translatable_object_using_activated_locales(
        $localeRepository,
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        LocaleInterface $enLocale,
        LocaleInterface $frLocale
    ) {
        $translatable->getTranslations()->willReturn([
            $english, $french
        ]);

        $english->getLocale()->willReturn('en_US');
        $english->getLabel()->willReturn('foo');

        $french->getLocale()->willReturn('fr_FR');
        $french->getLabel()->willReturn('bar');

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $frLocale->isActivated()->willReturn(true);

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
        $localeRepository,
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        AttributeTranslation $german,
        LocaleInterface $enLocale,
        LocaleInterface $frLocale,
        LocaleInterface $deLocale
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

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $frLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deLocale);
        $deLocale->isActivated()->willReturn(true);

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
        $localeRepository,
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        AttributeTranslation $german,
        LocaleInterface $enLocale,
        LocaleInterface $frLocale,
        LocaleInterface $deLocale
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


        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $frLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deLocale);
        $deLocale->isActivated()->willReturn(true);

        $this->shouldThrow('\LogicException')->duringNormalize($translatable, 'standard', [
            'locales'  => [],
            'property' => 'unknown_property'
        ]);
    }

    function it_does_not_export_disable_locale(
        $localeRepository,
        TranslatableInterface $translatable,
        AttributeTranslation $english,
        AttributeTranslation $french,
        AttributeTranslation $german,
        LocaleInterface $enLocale,
        LocaleInterface $frLocale,
        LocaleInterface $deLocale
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

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $frLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deLocale);
        $deLocale->isActivated()->willReturn(false);

        $this->normalize($translatable, 'standard', [
            'locales'  => [],
            'property' => 'label'
        ])->shouldReturn(
            [
                'en_US' => 'foo',
                'fr_FR' => 'bar',
            ]
        );
    }
}
