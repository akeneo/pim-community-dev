<?php

namespace spec\Pim\Bundle\LocalizationBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Component\Versioning\Model\Version;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterInterface;
use Pim\Component\Localization\Presenter\PresenterRegistryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VersionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $versionNormalizer,
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith(
            $versionNormalizer,
            $presenterRegistry,
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
        $presenterRegistry,
        PresenterInterface $numberPresenter,
        PresenterInterface $pricesPresenter,
        PresenterInterface $metricPresenter
    ) {
        $versionNormalizer->normalize($version, 'internal_api', Argument::any())->willReturn([
            'changeset' => [
                'maximum_frame_rate' => ['old' => '', 'new' => '200.7890'],
                'price-EUR'          => ['old' => '5.00', 'new' => '5.15'],
                'weight'             => ['old' => '', 'new' => '10.1234'],
            ]
        ]);

        $options = ['locale' => 'fr_FR'];
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');

        $presenterRegistry->getPresenterByAttributeCode('maximum_frame_rate')->willReturn($numberPresenter);
        $presenterRegistry->getPresenterByAttributeCode('price')->willReturn($pricesPresenter);
        $presenterRegistry->getPresenterByAttributeCode('weight')->willReturn($metricPresenter);

        $numberPresenter->present('200.7890', $options)->willReturn('200,7890');
        $pricesPresenter->present('5.00', $options)->willReturn('5,00');
        $pricesPresenter->present('5.15', $options)->willReturn('5,15');
        $metricPresenter->present('10.1234', $options)->willReturn('10,1234');

        $numberPresenter->present('', $options)->willReturn('');
        $pricesPresenter->present('', $options)->willReturn('');
        $metricPresenter->present('', $options)->willReturn('');

        $this->normalize($version, 'internal_api')->shouldReturn([
            'changeset' => [
                'maximum_frame_rate' => ['old' => '', 'new' => '200,7890'],
                'price-EUR'          => ['old' => '5,00', 'new' => '5,15'],
                'weight'             => ['old' => '', 'new' => '10,1234'],
            ]
        ]);
    }
}
