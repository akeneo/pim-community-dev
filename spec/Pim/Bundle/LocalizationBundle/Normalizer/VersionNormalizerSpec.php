<?php

namespace spec\Pim\Bundle\LocalizationBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Component\Versioning\Model\Version;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterAttributeConverter;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VersionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $versionNormalizer,
        PresenterAttributeConverter $converter,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith(
            $versionNormalizer,
            $converter,
            $localeResolver
        );
    }

    function it_supports_version_normalization(Version $version)
    {
        $this->supportsNormalization($version, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_fr_numbers(
        $version,
        $localeResolver,
        $versionNormalizer,
        $converter
    ) {
        $versionNormalizer->normalize($version, 'internal_api', Argument::any())->willReturn([
            'changeset' => [
                'maximum_frame_rate' => ['old' => '', 'new' => '200.7890'],
                'price-EUR'          => ['old' => '5.00', 'new' => '5.15'],
                'weight'             => ['old' => '', 'new' => '10.1234'],
            ]
        ]);
        $localeOptions = ['decimal_separator' => ',', 'date_format' => 'Y-m-d'];
        $localeResolver->getFormats()->willReturn($localeOptions);

        $converter
            ->convertDefaultToLocalizedValue('maximum_frame_rate', '200.7890', $localeOptions)
            ->willReturn('200,7890');
        $converter
            ->convertDefaultToLocalizedValue('price', '5.00', $localeOptions)
            ->willReturn('5,00');
        $converter
            ->convertDefaultToLocalizedValue('price', '5.15', $localeOptions)
            ->willReturn('5,15');
        $converter
            ->convertDefaultToLocalizedValue('weight', '10.1234', $localeOptions)
            ->willReturn('10,1234');
        $converter
            ->convertDefaultToLocalizedValue(Argument::any(), '', $localeOptions)
            ->willReturn('');

        $this->normalize($version, 'internal_api')->shouldReturn([
            'changeset' => [
                'maximum_frame_rate' => ['old' => '', 'new' => '200,7890'],
                'price-EUR'          => ['old' => '5,00', 'new' => '5,15'],
                'weight'             => ['old' => '', 'new' => '10,1234'],
            ]
        ]);
    }
}
