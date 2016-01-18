<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;
use Prophecy\Argument;

class GroupDenormalizerSpec extends ObjectBehavior
{
    const ENTITY_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Group';
    const FORMAT_CSV   = 'csv';

    function let(
        ManagerRegistry $registry,
        GroupRepositoryInterface $groupRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $registry->getRepository(self::ENTITY_CLASS)->willReturn($groupRepository);

        $this->beConstructedWith($registry, self::ENTITY_CLASS, $groupTypeRepository, $attributeRepository);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_is_serializer_aware()
    {
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
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

    function it_denormalizes_group($groupRepository, Group $group)
    {
        $groupRepository->findOneByIdentifier('foo')->willReturn($group);

        $this->denormalize('foo', self::ENTITY_CLASS, self::FORMAT_CSV)->shouldReturn($group);
    }

    function it_denormalizes_a_new_group_with_immutable_properties(
        $groupRepository,
        $groupTypeRepository,
        $attributeRepository,
        Group $group,
        GroupType $type,
        AttributeInterface $size,
        AttributeInterface $color,
        GroupTranslation $translationUS
    ) {
        $groupRepository->findOneByIdentifier('tshirt')->willReturn(null);
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($type);
        $attributeRepository->findOneByIdentifier('size')->willReturn($size);
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);

        $group->getId()->willReturn(null);
        $group->setCode('tshirt')->shouldBeCalled();
        $group->setType($type)->shouldBeCalled();
        $group->setAxisAttributes([$color, $size])->shouldBeCalled();

        $group->getTranslation('en_US')->willReturn($translationUS);
        $translationUS->setLabel('My T-shirt')->shouldBeCalled();
        $group->addTranslation($translationUS)->shouldBeCalled();

        $this->denormalize(
            [
                'code' => 'tshirt',
                'type' => 'VARIANT',
                'axis' => 'color,size',
                'label-en_US' => 'My T-shirt'
            ],
            self::ENTITY_CLASS,
            self::FORMAT_CSV,
            ['entity' => $group]
        )->shouldReturn($group);
    }

    function it_denormalizes_an_existing_group_with_properties(
        $groupRepository,
        Group $group,
        GroupType $type,
        AttributeInterface $size,
        AttributeInterface $color,
        GroupTranslation $translationUS
    ) {
        $groupRepository->findOneByIdentifier('tshirt')->willReturn(null);

        $group->getId()->willReturn(42);
        $group->setCode('tshirt')->shouldBeCalled();
        $group->setType(Argument::any())->shouldNotBeCalled();
        $group->setAttributes(Argument::any())->shouldNotBeCalled();

        $group->getTranslation('en_US')->willReturn($translationUS);
        $translationUS->setLabel('My T-shirt')->shouldBeCalled();
        $group->addTranslation($translationUS)->shouldBeCalled();

        $this->denormalize(
            [
                'code' => 'tshirt',
                'label-en_US' => 'My T-shirt'
            ],
            self::ENTITY_CLASS,
            self::FORMAT_CSV,
            ['entity' => $group]
        )->shouldReturn($group);
    }

    function it_throws_an_exception_if_group_is_unknown($groupRepository)
    {
        $groupRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            new \Exception(
                sprintf('Entity "%s" with identifier "%s" not found', self::ENTITY_CLASS, 'foo')
            )
        )->during('denormalize', ['foo', self::ENTITY_CLASS, self::FORMAT_CSV]);
    }
}
