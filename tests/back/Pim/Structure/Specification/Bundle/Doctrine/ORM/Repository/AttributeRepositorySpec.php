<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Prophecy\Argument;

class AttributeRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->name = 'attribute';
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_attribute_repository()
    {
        $this->shouldImplement(AttributeRepositoryInterface::class);
    }

    function it_finds_the_axis_attribute(
        $em,
        QueryBuilder $queryBuilder,
        Expr $in,
        Expr $notScopable,
        Expr $notLocalizable,
        AbstractQuery $query
    ) {
        $queryBuilder->expr()->willreturn($in, $notScopable, $notLocalizable);
        $in->in('a.type', [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT])
            ->willReturn($in);
        $notScopable->neq('a.scopable', 1)->willReturn($notScopable);
        $notLocalizable->neq('a.localizable', 1)->willReturn($notLocalizable);


        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('a')->willReturn($queryBuilder);
        $queryBuilder->select('a.id')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', a.code, \']\')) as label')->willReturn($queryBuilder);
        $queryBuilder->addSelect('a.code')->willReturn($queryBuilder);
        $queryBuilder->from('attribute', 'a', null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('a.translations', 't')->willReturn($queryBuilder);
        $queryBuilder->andWhere($in)->willReturn($queryBuilder);
        $queryBuilder->andWhere($notScopable)->willReturn($queryBuilder);
        $queryBuilder->andWhere($notLocalizable)->willReturn($queryBuilder);
        $queryBuilder->andWhere('t.locale = :locale')->willReturn($queryBuilder);
        $queryBuilder->setParameter('locale', 'en_US')->willReturn($queryBuilder);
        $queryBuilder->orderBy('t.label')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            ['id' => 11, 'label' => 'a'],
            ['id' => 12, 'label' => 'b'],
            ['id' => 10, 'label' => 's'],
        ]);

        $this->findAvailableAxes('en_US')->shouldReturn([
            'a' => 11,
            'b' => 12,
            's' => 10,
        ]);
    }
}
