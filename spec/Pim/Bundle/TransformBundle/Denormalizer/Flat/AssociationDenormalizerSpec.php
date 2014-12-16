<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Serializer;

class AssociationDenormalizerSpec extends ObjectBehavior
{
    const ENTITY_CLASS     = 'Pim\Bundle\CatalogBundle\Model\Association';
    const ASSOC_TYPE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
    const GROUP_CLASS      = 'Pim\Bundle\CatalogBundle\Entity\Group';
    const PRODUCT_CLASS    = 'Pim\Bundle\CatalogBundle\Model\ProductInterface';

    const FORMAT_CSV    = 'csv';

    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith(
            $registry,
            self::ENTITY_CLASS,
            self::ASSOC_TYPE_CLASS,
            self::PRODUCT_CLASS,
            self::GROUP_CLASS
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_is_serializer_aware()
    {
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_denormalization_in_csv_of_association()
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

    function it_throws_exception_if_part_is_not_passed_in_the_context()
    {
        $exception = new \Exception(
            'Missing key "part" in context explaining if denormalizing groups or products part of the association'
        );
        $this
            ->shouldThrow($exception)
            ->during('denormalize', [[], self::ENTITY_CLASS, self::FORMAT_CSV, []]);
    }

    function it_throws_exception_if_entity_or_association_type_code_is_not_passed_in_context()
    {
        $this
            ->shouldThrow(
                new \Exception('Association entity or association type code should be passed in context"')
            )
            ->during('denormalize', ['foo', self::ENTITY_CLASS, self::FORMAT_CSV, ['part' => 'groups']]);

        $this
            ->shouldThrow(
                new \Exception('Association entity or association type code should be passed in context"')
            )
            ->during(
                'denormalize',
                ['foo', self::ENTITY_CLASS, self::FORMAT_CSV, ['part' => 'groups', 'entity' => null]]
            );
    }

    function it_denormalizes_association_from_association_in_context(
        Association $association,
        Serializer $serializer,
        Group $group
    ) {
        $serializer->denormalize('foo', self::GROUP_CLASS, self::FORMAT_CSV)->willReturn($group);
        $association->addGroup($group)->shouldBeCalled();

        $this->setSerializer($serializer);
        $this->denormalize('foo', self::ENTITY_CLASS, self::FORMAT_CSV, ['part' => 'groups', 'entity' => $association]);
    }

    function it_denormalizes_association_with_products(
        Association $association,
        Serializer $serializer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        // Mock product denormalization
        $serializer->denormalize('foo', self::PRODUCT_CLASS, self::FORMAT_CSV)->willReturn($product1);
        $serializer->denormalize('bar', self::PRODUCT_CLASS, self::FORMAT_CSV)->willReturn($product2);
        $association->addProduct($product1)->shouldBeCalled();
        $association->addProduct($product2)->shouldBeCalled();

        $this->setSerializer($serializer);
        $this->denormalize(
            'foo,bar',
            self::ENTITY_CLASS,
            self::FORMAT_CSV,
            ['part' => 'products', 'entity' => $association]
        );
    }
}
