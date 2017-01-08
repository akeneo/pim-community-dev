<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Component\Catalog\Model\FamilyInterface;

class FamilyDeletedQueryGeneratorSpec extends ObjectBehavior
{
    public function let(NamingUtility $namingUtility)
    {
        $this->beConstructedWith($namingUtility, Family::class);
    }

    public function it_generates_a_query_to_delete_product_family(FamilyInterface $family)
    {
        $family->getCode()->willReturn(42);
        $this->generateQuery($family, '', '', '')->shouldReturn([
            [
                ['normalizedData.family.code' => 42],
                ['$unset' => ['normalizedData.family' => '']],
                ['multiple' => true]
            ]
        ]);
    }
}
