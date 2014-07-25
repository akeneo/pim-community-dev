<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;

class GroupDenormalizerSpec extends ObjectBehavior
{
    const ENTITY_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Group';
    const FORMAT_CSV   = 'csv';

    function let(ManagerRegistry $registry, GroupRepository $repository)
    {
        $registry->getRepository(self::ENTITY_CLASS)->willReturn($repository);

        $this->beConstructedWith($registry, self::ENTITY_CLASS);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_in_csv_of_a_group()
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

    function it_denormalizes_group($repository, Group $group)
    {
        $repository->findByReference('foo')->willReturn($group);

        $this->denormalize('foo', self::ENTITY_CLASS, self::FORMAT_CSV)->shouldReturn($group);
    }

    function it_throws_an_exception_if_group_is_unknown($repository)
    {
        $repository->findByReference('foo')->willReturn(false);

        $this->shouldThrow(
            new \Exception(
                sprintf('Entity "%s" with identifier "%s" not found', self::ENTITY_CLASS, 'foo')
            )
        )->during('denormalize', ['foo', self::ENTITY_CLASS, self::FORMAT_CSV]);
    }
}
