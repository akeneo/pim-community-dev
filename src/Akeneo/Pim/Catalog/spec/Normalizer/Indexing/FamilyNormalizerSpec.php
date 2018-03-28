<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductPrice;
use Pim\Component\Catalog\Normalizer\Indexing\FamilyNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
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
        $this->supportsNormalization($family, 'standard')->shouldReturn(false);
        $this->supportsNormalization($family, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(true);
        $this->supportsNormalization($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization($price, 'standard')->shouldReturn(false);
        $this->supportsNormalization($price, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(false);
        $this->supportsNormalization($price, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
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
