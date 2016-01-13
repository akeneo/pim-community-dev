<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Form\Transformer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;

class AssetCollectionToStringTransformerSpec extends ObjectBehavior
{
    public function let(AssetRepositoryInterface $assetRepository)
    {
        $this->beConstructedWith($assetRepository);
    }

    public function it_transform_an_asset_collection_into_string(AssetInterface $paint, AssetInterface $dog)
    {
        $paint->getCode()->willReturn('paint');
        $dog->getCode()->willReturn('dog');
        $this->transform([$paint, $dog])->shouldReturn('paint,dog');

        $this->transform(null)->shouldReturn('');
        $this->shouldThrow('Symfony\Component\Form\Exception\TransformationFailedException')->during('transform', [[$paint, null]]);
    }

    public function it_reverse_transform_a_string_into_an_asset_collection($assetRepository, AssetInterface $paint, AssetInterface $dog)
    {
        $assetRepository->findByIdentifiers(['dog', 'paint'])->willReturn([$dog, $paint]);
        $this->reverseTransform('dog,paint')->shouldReturn([$dog, $paint]);

        $this->reverseTransform('')->shouldReturn([]);
        $this->reverseTransform(null)->shouldReturn([]);
    }
}
