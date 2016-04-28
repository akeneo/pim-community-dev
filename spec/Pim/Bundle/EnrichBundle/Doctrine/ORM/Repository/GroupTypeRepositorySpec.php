<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;

class GroupTypeRepositorySpec extends ObjectBehavior
{
    function let(UserContext $userContext, EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'group_type';

        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $em->getClassMetadata('groupTypeClass')->willReturn($classMetadata);

        $this->beConstructedWith($userContext, $em, 'groupTypeClass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\GroupTypeRepository');
    }

    function it_is_a_group_repository()
    {
        $this->shouldImplement('Pim\Component\Enrich\Repository\ChoicesProviderInterface');
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_finds_group_types_to_build_select($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('g')->willReturn($queryBuilder);
        $queryBuilder->select('g.id')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(t.label, CONCAT(\'[\', g.code, \']\')) as label')->willReturn($queryBuilder);
        $queryBuilder->from('group_type', 'g')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('g.translations', 't')->willReturn($queryBuilder);
        $queryBuilder->andWhere('g.variant = :is_variant')->willReturn($queryBuilder);
        $queryBuilder->andWhere('t.locale = :locale')->willReturn($queryBuilder);
        $queryBuilder->orderBy('t.label')->willReturn($queryBuilder);
        $queryBuilder->setParameter('locale', 'en_US')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            ['id' => 10, 'label' => 'group fr'],
            ['id' => 10, 'label' => 'group en'],
            ['id' => 11, 'label' => '[group_other_code]'],
        ]);

        $this->findChoices()->shouldReturn([
            10 => 'group en',
            11 => '[group_other_code]',
        ]);
    }
}
