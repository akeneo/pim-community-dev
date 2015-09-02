<?php

namespace spec\PimEnterprise\Component\ProductAsset\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
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

    function it_should_normalize($normalizer, AssetInterface $asset)
    {
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

    function it_should_normalize_inVersioning_mode(
        $normalizer,
        AssetInterface $asset,
        VariationInterface $variation1,
        VariationInterface $variation2,
        ReferenceInterface $reference1,
        ReferenceInterface $reference2,
        FileInfoInterface $file1,
        FileInfoInterface $file2,
        FileInfoInterface $file3,
        FileInfoInterface $file4,
        ArrayCollection $references
    ) {
        $normalizedValues = [
            'code'        => 'code',
            'description' => 'my description',
            'enabled'     => true,
            'end_of_use'  => '2010-10-10',
            'created_at'  => '2017-10-11',
            'updated_at'  => '2013-10-12'
        ];

        $result = $normalizedValues + [
            'tags'       => 'tag1,tag2,tag3',
            'categories' => 'cat1,cat2,cat3',
            'references' => ['reference_1', 'reference_2'],
            'variations' => ['variation_1', 'variation_2'],
        ];

        $normalizer->normalize($asset, 'csv', ['versioning' => true])->willReturn($normalizedValues);
        $asset->getCode()->willReturn('code');
        $asset->getDescription()->willReturn('my description');
        $asset->isEnabled()->willReturn(true);
        $asset->getEndOfUseAt()->willReturn(new \Datetime('2010-10-10'));
        $asset->getCreatedAt()->willReturn(new \Datetime('2017-10-11'));
        $asset->getUpdatedAt()->willReturn(new \Datetime('2013-10-12'));
        $asset->getTagCodes()->willReturn('tag1,tag2,tag3');
        $asset->getCategoryCodes()->willReturn('cat1,cat2,cat3');
        $asset->getVariations()->willReturn([$variation1, $variation2]);
        $variation1->getFileInfo()->willReturn($file1);
        $variation2->getFileInfo()->willReturn($file2);
        $file1->getKey()->willReturn('variation_1');
        $file2->getKey()->willReturn('variation_2');
        $asset->getReferences()->willReturn($references);
        $references->toArray()->willReturn([$reference1, $reference2]);
        $reference1->getFileInfo()->willReturn($file3);
        $reference2->getFileInfo()->willReturn($file4);
        $file3->getKey()->willReturn('reference_1');
        $file4->getKey()->willReturn('reference_2');

        $this->normalize($asset, 'csv', ['versioning' => true])->shouldReturn($result);
    }
}
