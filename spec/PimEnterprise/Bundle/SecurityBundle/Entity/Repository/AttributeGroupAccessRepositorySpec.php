<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Prophecy\Argument;

class AttributeGroupAccessRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = 'PimEnterprise\Bundle\SecurityBundle\Entity';
        $this->beConstructedWith($em, $class);
    }

    function it_returns_granted_attribute_ids_with_filterable_ids(
        $em,
        QueryBuilder $qb,
        Expr $expr,
        ArrayCollection $groups,
        AbstractQuery $query,
        UserInterface $user
    ) {
        $user->getGroups()->willReturn($groups);
        $accessLevel = Attributes::VIEW_ATTRIBUTES;
        $filterableIds = [1, 2];

        $query = $this->buildGrantedAttributeQuery($em, $qb, $query, $expr, $filterableIds);
        $query->getArrayResult()->willReturn([
            0 => ['id' => 1],
            1 => ['id' => 2]
        ]);

        $this->getGrantedAttributeIds($user, $accessLevel, $filterableIds)->shouldReturn([1, 2]);
    }

    function it_returns_granted_attribute_ids_without_filterable_ids(
        $em,
        QueryBuilder $qb,
        Expr $expr,
        ArrayCollection $groups,
        AbstractQuery $query,
        UserInterface $user
    ) {
        $user->getGroups()->willReturn($groups);
        $accessLevel = Attributes::VIEW_ATTRIBUTES;
        $filterableIds = [];

        $query = $this->buildGrantedAttributeQuery($em, $qb, $query, $expr, $filterableIds);
        $query->getArrayResult()->willReturn([]);

        $this->getGrantedAttributeIds($user, $accessLevel, $filterableIds)->shouldReturn([]);
    }

    function it_throws_an_exception_if_filterable_ids_is_null(UserInterface $user)
    {
        $accessLevel = Attributes::VIEW_ATTRIBUTES;

        $this
            ->shouldThrow('Exception')
            ->duringGetGrantedAttributeIds($user, $accessLevel, null);
    }

    private function buildGrantedAttributeQuery(
        EntityManager $em,
        QueryBuilder $qb,
        AbstractQuery $query,
        Expr $expr,
        $filterableIds
    ) {
        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('aga')->willReturn($qb);
        $qb->select('ag.id')->willReturn($qb);
        $qb->select('a.id')->willReturn($qb);
        $qb->from('PimEnterprise\Bundle\SecurityBundle\Entity', 'aga')->willReturn($qb);
        $qb->innerJoin('aga.attributeGroup', 'ag', 'ag.id')->willReturn($qb);
        $qb->innerJoin('ag.attributes', 'a')->willReturn($qb);
        $qb->andWhere(null)->willReturn($qb);
        $qb->andWhere($expr->in('aga.userGroup', ':groups'))->willReturn($qb);
        $qb->andWhere(Argument::any(), true)->willReturn($qb);
        $qb->andWhere($expr->in('a.id', $filterableIds));
        $qb->setParameter('groups', null)->willReturn($qb);
        $qb->resetDQLParts(['select'])->willReturn($qb);
        $qb->groupBy('a.id')->willReturn($qb);

        $qb->expr()->willReturn($expr);

        $qb->getQuery()->willReturn($query);

        return $query;
    }
}
