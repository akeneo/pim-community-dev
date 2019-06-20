<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetCollectionData;
use PhpSpec\ObjectBehavior;

class AssetCollectionDataSpec extends ObjectBehavior
{
    public function let(
        AssetCode $starckAssetCode,
        AssetCode $breuerAssetCode
    ) {
        $starckAssetCode->__toString()->willReturn('starck');
        $breuerAssetCode->__toString()->willReturn('breuer');

        $this->beConstructedThrough('fromAssetCodes', [[$starckAssetCode, $breuerAssetCode]]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', [['breuer', 'paul']]);
        $this->shouldBeAnInstanceOf(AssetCollectionData::class);
    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_array()
    {
        $this->beConstructedThrough('createFromNormalize', ['Hello']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_array()
    {
        $this->beConstructedThrough('createFromNormalize', [[]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_something_else_than_a_asset_code(
        AssetIdentifier $starckAssetIdentifier
    ) {
        $this->beConstructedThrough('fromAssetCodes', [[$starckAssetIdentifier]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(['starck', 'breuer']);
    }
}
