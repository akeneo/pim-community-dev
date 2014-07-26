<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssociationDenormalizerSpec extends ObjectBehavior
{
    const ENTITY_CLASS  = 'Pim\Bundle\CatalogBundle\Model\Association';
    const GROUP_CLASS   = 'Pim\Bundle\CatalogBundle\Entity\Group';
    const PRODUCT_CLASS = 'Pim\Bundle\CatalogBundle\Model\Product';

    const FORMAT_CSV    = 'csv';

    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry, self::ENTITY_CLASS, self::PRODUCT_CLASS, self::GROUP_CLASS);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
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
}
