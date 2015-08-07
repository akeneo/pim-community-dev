<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
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

    function it_should_normalize_with_no_references_and_no_categories($normalizer, AssetInterface $asset)
    {
        $normalizedValues = [
            'code'        => 'code',
            'description' => 'my description',
            'enabled'     => true,
            'end_of_use'  => '2010-10-10',
            'created_at'  => '2017-10-11',
            'updated_at'  => '2013-10-12',
        ];

        $result = $normalizedValues + ['references' => [], 'categories' => []];

        $normalizer->normalize($asset, null, [])->willReturn($normalizedValues);
        $asset->getCode()->willReturn('code');
        $asset->getDescription()->willReturn('my description');
        $asset->isEnabled()->willReturn(true);
        $asset->getEndOfUseAt()->willReturn(new \Datetime('2010-10-10'));
        $asset->getCreatedAt()->willReturn(new \Datetime('2017-10-11'));
        $asset->getUpdatedAt()->willReturn(new \Datetime('2013-10-12'));
        $asset->getTagCodes()->willReturn('tag1,tag2,tag3');
        $asset->getCategoryCodes()->willReturn('cat1,cat2,cat3');
        $asset->getReferences()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $this->normalize($asset)->shouldReturn($result);
    }
}
