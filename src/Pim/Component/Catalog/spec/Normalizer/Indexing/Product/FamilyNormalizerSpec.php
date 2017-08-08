<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductPrice;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\FamilyNormalizer;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $translationNormalizer, LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($translationNormalizer, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_families_in_indexing_format(FamilyInterface $family, ProductPrice $price)
    {
        $this->supportsNormalization($family, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($family, 'standard')->shouldReturn(false);
        $this->supportsNormalization($price, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($price, 'standard')->shouldReturn(false);
    }

    function it_normalizes_families($translationNormalizer, $localeRepository, FamilyInterface $family)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['de_DE']);
        $family->getCode()->willReturn('camcorders');
        $translationNormalizer->normalize($family, 'indexing', ['locales' => ['de_DE']])->willReturn([
            'fr_FR' => 'La famille des caméras',
            'en_US' => 'Camcorders family',
            'de_DE' => null,
        ]);

        $this->normalize($family, 'indexing')->shouldReturn(
            [
                'code'   => 'camcorders',
                'labels' => [
                    'fr_FR' => 'La famille des caméras',
                    'en_US' => 'Camcorders family',
                    'de_DE' => null,
                ],
            ]
        );
    }
}
