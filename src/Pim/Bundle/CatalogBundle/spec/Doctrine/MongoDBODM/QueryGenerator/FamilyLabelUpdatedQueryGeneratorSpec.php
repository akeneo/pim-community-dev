<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;
use Pim\Component\Catalog\Model\FamilyInterface;

class FamilyLabelUpdatedQueryGeneratorSpec extends ObjectBehavior
{
    function let(NamingUtility $namingUtility)
    {
        $this->beConstructedWith($namingUtility, 'Pim\Bundle\CatalogBundle\Model\FamilyTranslation', 'label');
    }

    function it_generates_a_query_to_update_product_families(FamilyTranslation $shirt, FamilyInterface $family)
    {
        $shirt->getForeignKey()->willReturn($family);
        $family->getId()->willReturn(12);
        $shirt->getLocale()->willReturn('fr_FR');

        $this->generateQuery($shirt, 'label', 'sku', 'name')->shouldReturn([[
            ['family'   => 12],
            ['$set'     => ['normalizedData.family.labels.fr_FR' => 'name']],
            ['multiple' => true]
        ]]);
    }
}
