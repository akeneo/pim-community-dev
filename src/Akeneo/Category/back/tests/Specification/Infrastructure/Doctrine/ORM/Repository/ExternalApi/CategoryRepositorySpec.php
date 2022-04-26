<?php

namespace Specification\Akeneo\Category\Infrastructure\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;

class CategoryRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        ClassMetadata $classMetadata,
        CategoryRepositoryInterface $categoryRepository
    ) {

        $classMetadata->name = 'category';
        $entityManager->getClassMetadata('category')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'category', $categoryRepository);
    }

    function it_fails_on_filter_validation_with_wrong_operator_for_updated(
        EntityManager $entityManager,
        QueryBuilder $queryBuilder
    ) {
        $queryBuilder->select('r')->willReturn($queryBuilder);
        $queryBuilder->from('category', 'r', null)->willReturn($queryBuilder);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);

        $this->shouldThrow(\InvalidArgumentException::class)->during('searchAfterOffset', [
            ['updated' => [['operator' => 'BadOperator', 'value' => '2019-06-09T12:00:00+00:00']]],
            ['code' => 'ASC'],
            10,
            0
        ]);
    }

    function it_fails_on_filter_validation_with_wrong_date_format_for_updated(
        EntityManager $entityManager,
        QueryBuilder $queryBuilder
    ) {
        $queryBuilder->select('r')->willReturn($queryBuilder);
        $queryBuilder->from('category', 'r', null)->willReturn($queryBuilder);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);

        $this->shouldThrow(\InvalidArgumentException::class)->during('searchAfterOffset', [
            ['updated' => [['operator' => '>', 'value' => '2019-06-09 12:00:00']]],
            ['code' => 'ASC'],
            10,
            0
        ]);
    }
}
