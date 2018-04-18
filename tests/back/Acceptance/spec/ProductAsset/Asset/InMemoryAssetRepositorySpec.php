<?php

namespace spec\AkeneoEnterprise\Test\Acceptance\ProductAsset\Asset;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use AkeneoEnterprise\Test\Acceptance\ProductAsset\Asset\InMemoryAssetRepository;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;

class InMemoryAssetRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryAssetRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_is_an_asset_repository()
    {
        $this->shouldBeAnInstanceOf(AssetRepositoryInterface::class);
    }
    
    function it_asserts_the_identifier_property_is_the_code()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_asset_by_identifier()
    {
        $asset = new Asset();
        $asset->setCode('an-asset');
        $this->beConstructedWith([$asset->getCode() => $asset]);

        $this->findOneByIdentifier('an-asset')->shouldReturn($asset);
    }

    function it_finds_one_asset_by_its_code()
    {
        $asset = new Asset();
        $asset->setCode('asset');
        $this->beConstructedWith([$asset->getCode() => $asset]);

        $this->findOneByCode('asset')->shouldReturn($asset);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-asset')->shouldReturn(null);
    }

    function it_saves_a_asset()
    {
        $asset = new Asset();
        $asset->setCode('an-asset');

        $this->save($asset)->shouldReturn(null);

        $this->findOneByIdentifier($asset->getCode())->shouldReturn($asset);
    }

    function it_saves_only_assets()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }
}
