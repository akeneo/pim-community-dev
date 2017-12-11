<?php

namespace spec\PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Repository\SearchAfterPageableRepositoryInterface;
use PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository\AssetRepository;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

class AssetRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $em,
        AssetRepositoryInterface $assetRepository,
        ClassMetadata $classMetadata
    ) {

        $em->getClassMetadata(AssetInterface::class)->willReturn($classMetadata);
        $this->beConstructedWith(
            $em,
            AssetInterface::class,
            $assetRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetRepository::class);
    }

    function it_is_an_identifiable_object_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_pagineable_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_search_after_pagineable_repository()
    {
        $this->shouldImplement(SearchAfterPageableRepositoryInterface::class);
    }

    function it_finds_by_identifier($assetRepository, AssetInterface $asset)
    {
        $assetRepository->findOneByIdentifier('foo')->willReturn($asset);

        $this->findOneByIdentifier('foo');
    }

    function it_gets_identifier_property($assetRepository)
    {
        $assetRepository->getIdentifierProperties()->willReturn(['code']);

        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_searches_with_offset_pagination($assetRepository)
    {
        $assetRepository->getIdentifierProperties()->willReturn(['code']);

        $this->getIdentifierProperties()->shouldReturn(['code']);
    }
}
