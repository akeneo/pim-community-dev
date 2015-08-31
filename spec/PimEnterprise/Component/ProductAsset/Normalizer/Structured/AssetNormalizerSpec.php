<?php

namespace spec\PimEnterprise\Component\ProductAsset\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class AssetNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_should_normalize(AssetInterface $asset)
    {
        $normalizedValues = [
            'code'        => 'code',
            'localized'   => 0,
            'description' => 'my description',
            'end_of_use'  => '2010-10-10',
        ];

        $asset->getCode()->willReturn('code');
        $asset->getDescription()->willReturn('my description');
        $asset->getEndOfUseAt()->willReturn(new \Datetime('2010-10-10'));
        $asset->isLocalizable()->willReturn(false);

        $this->normalize($asset)->shouldReturn($normalizedValues);
    }
}
