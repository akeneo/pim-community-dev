<?php

namespace spec\PimEnterprise\Component\ProductAsset\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Normalizer\Standard\DateTimeNormalizer;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class AssetNormalizerSpec extends ObjectBehavior
{
    function let(DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->beConstructedWith($dateTimeNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Normalizer\Standard\AssetNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(AssetInterface $asset)
    {
        $this->supportsNormalization($asset, 'standard')->shouldBe(true);
        $this->supportsNormalization($asset, 'json')->shouldBe(false);
        $this->supportsNormalization($asset, 'xml')->shouldBe(false);
    }

    function it_normalizes_empty_asset($dateTimeNormalizer, AssetInterface $asset)
    {
        $dateTimeNormalizer->normalize(null, 'standard', [])->willReturn(null);

        $asset->getCode()->willReturn('code');
        $asset->getDescription()->willReturn('');
        $asset->getEndOfUseAt()->willReturn(null);
        $asset->isLocalizable()->willReturn(false);
        $asset->getTagCodes()->willReturn([]);
        $asset->getCategoryCodes()->willReturn([]);

        $this->normalize($asset)->shouldReturn([
            'code'        => 'code',
            'localizable' => false,
            'description' => null,
            'end_of_use'  => null,
            'tags'        => [],
            'categories'  => []
        ]);
    }

    function it_normalizes_asset($dateTimeNormalizer, AssetInterface $asset)
    {
        $date = new \DateTime('2016-08-22 15:54:22');
        $dateTimeNormalizer->normalize($date, 'standard', [])->willReturn('2016-08-22T15:54:22+01:00');

        $asset->getCode()->willReturn('code');
        $asset->getDescription()->willReturn('my description');
        $asset->getEndOfUseAt()->willReturn($date);
        $asset->isLocalizable()->willReturn(true);
        $asset->getTagCodes()->willReturn(['cats', 'dogs']);
        $asset->getCategoryCodes()->willReturn(['pets']);

        $this->normalize($asset)->shouldReturn([
            'code'        => 'code',
            'localizable' => true,
            'description' => 'my description',
            'end_of_use'  => '2016-08-22T15:54:22+01:00',
            'tags'        => ['cats', 'dogs'],
            'categories'  => ['pets']
        ]);
    }
}
