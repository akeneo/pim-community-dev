<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Prophecy\Argument;
use Symfony\Component\Serializer\Serializer;

class FamilyDenormalizerSpec extends ObjectBehavior
{
    const ENTITY_CLASS = 'Pim\Bundle\CatalogBundle\Model\FamilyInterface';
    const FORMAT_CSV   = 'csv';

    function let(Serializer $serializer, ManagerRegistry $registry, FamilyRepository $repository)
    {
        $registry->getRepository(self::ENTITY_CLASS)->willReturn($repository);

        $this->beConstructedWith($registry, self::ENTITY_CLASS);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_is_serializer_aware()
    {
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_denormalization_in_csv_of_a_family()
    {
        $this->supportsDenormalization([], self::ENTITY_CLASS, self::FORMAT_CSV)->shouldBe(true);

        $this->supportsDenormalization(
            [],
            Argument::not(self::ENTITY_CLASS),
            self::FORMAT_CSV
        )->shouldBe(false);

        $this->supportsDenormalization(
            [],
            self::ENTITY_CLASS,
            Argument::not(self::FORMAT_CSV)
        )->shouldBe(false);

        $this->supportsDenormalization(
            [],
            Argument::not(self::ENTITY_CLASS),
            Argument::not(self::FORMAT_CSV)
        )->shouldBe(false);
    }

    function it_denormalizes_family($repository, FamilyInterface $family)
    {
        $repository->findByReference('foo')->willReturn($family);

        $this->denormalize('foo', self::ENTITY_CLASS, self::FORMAT_CSV)->shouldReturn($family);
    }

    function it_throws_an_exception_if_family_is_unknown($repository)
    {
        $repository->findByReference('foo')->willReturn(false);

        $this->shouldThrow(
            new \Exception(
                sprintf('Entity "%s" with identifier "%s" not found', self::ENTITY_CLASS, 'foo')
            )
        )->during('denormalize', ['foo', self::ENTITY_CLASS, self::FORMAT_CSV]);
    }
}
