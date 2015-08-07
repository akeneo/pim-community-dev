<?php

namespace spec\PimEnterprise\Component\ProductAsset\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_should_normalize($normalizer, AssetInterface $asset) {
        $normalizedValues = [
            'code'        => 'code',
            'description' => 'my description',
            'enabled'     => true,
            'end_of_use'  => '2010-10-10',
            'created_at'  => '2017-10-11',
            'updated_at'  => '2013-10-12',
        ];

        $result = $normalizedValues + ['tags' => 'tag1,tag2,tag3', 'categories' => 'cat1,cat2,cat3'];

        $normalizer->normalize($asset, null, [])->willReturn($normalizedValues);
        $asset->getCode()->willReturn('code');
        $asset->getDescription()->willReturn('my description');
        $asset->isEnabled()->willReturn(true);
        $asset->getEndOfUseAt()->willReturn(new \Datetime('2010-10-10'));
        $asset->getCreatedAt()->willReturn(new \Datetime('2017-10-11'));
        $asset->getUpdatedAt()->willReturn(new \Datetime('2013-10-12'));
        $asset->getTagCodes()->willReturn('tag1,tag2,tag3');
        $asset->getCategoryCodes()->willReturn('cat1,cat2,cat3');

        $this->normalize($asset)->shouldReturn($result);
    }
}
