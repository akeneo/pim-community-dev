<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetData;
use PhpSpec\ObjectBehavior;

class AssetDataSpec extends ObjectBehavior
{
    public function let(AssetCode $assetCode)
    {
        $assetCode->__toString()->willReturn('starck');

        $this->beConstructedThrough('fromAssetCode', [$assetCode]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AssetData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', ['breuer']);
        $this->shouldBeAnInstanceOf(AssetData::class);
    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_string()
    {
        $this->beConstructedThrough('createFromNormalize', [null]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_asset_code()
    {
        $this->beConstructedThrough('createFromNormalize', ['']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('starck');
    }

    /**
     * @see https://akeneo.atlassian.net/browse/PIM-8294
     */
    public function it_can_contain_the_zero_string()
    {
        $this->beConstructedThrough('createFromNormalize', ['0']);
        $this->normalize()->shouldReturn('0');
    }
}
