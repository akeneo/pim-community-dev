<?php

namespace Specification\Akeneo\Category\Infrastructure\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        ClassMetadata $classMetadata,
        CategoryRepositoryInterface $categoryRepository,
        ValidatorInterface $validator
    ) {

        $classMetadata->name = 'category';
        $entityManager->getClassMetadata('category')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'category', $categoryRepository, $validator);
    }

    function it_fails_on_filter_validation_with_wrong_operator_for_updated(
        EntityManager $entityManager,
        QueryBuilder $queryBuilder,
        ValidatorInterface $validator
    ) {
        $queryBuilder->select('r')->willReturn($queryBuilder);
        $queryBuilder->from('category', 'r', null)->willReturn($queryBuilder);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $validator->validate(Argument::any(), Argument::any())->willReturn([]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('searchAfterOffset', [
            ['updated' => [['operator' => 'BadOperator', 'value' => '2019-06-09T12:00:00+00:00']]],
            ['code' => 'ASC'],
            10,
            0
        ]);
    }

    function it_fails_on_filter_validation_with_wrong_date_format_for_updated(
        EntityManager $entityManager,
        QueryBuilder $queryBuilder,
        ValidatorInterface $validator,
        ConstraintViolation $violation
    ) {
        $queryBuilder->select('r')->willReturn($queryBuilder);
        $queryBuilder->from('category', 'r', null)->willReturn($queryBuilder);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $validator->validate(Argument::any(), Argument::any())->willReturn([$violation]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('searchAfterOffset', [
            ['updated' => [['operator' => '>', 'value' => '2019-06-09 12:00:00']]],
            ['code' => 'ASC'],
            10,
            0
        ]);
    }
}
