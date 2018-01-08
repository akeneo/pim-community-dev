<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\AttributeIsAFamilyVariantAxis;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Prophecy\Argument;

class AttributeIsAFamilyVariantAxisSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIsAFamilyVariantAxis::class);
    }

    function it_checks_if_an_attribute_is_used_as_variant_axis(
        $entityManager,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->from(VariantAttributeSetInterface::class, 'attribute_set')->willReturn($queryBuilder);
        $queryBuilder->select('COUNT(attribute_set.id)')->willReturn($queryBuilder);
        $queryBuilder->innerJoin('attribute_set.axes', 'attribute')->willReturn($queryBuilder);
        $queryBuilder->where('attribute.code = :attribute_code')->willReturn($queryBuilder);
        $queryBuilder->setParameter('attribute_code', 'size')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);

        $query->getSingleScalarResult()->willReturn(1);

        $this->execute('size')->shouldReturn(true);
    }
}
