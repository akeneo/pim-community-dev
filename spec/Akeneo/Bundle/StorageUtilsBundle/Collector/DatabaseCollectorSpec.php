<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Collector;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DatabaseCollectorSpec extends ObjectBehavior
{
    /** @var int the max number of product value allowed before having to switch to MongoBD */
    const MYSQL_PRODUCT_VALUE_LIMIT = 5000000;

    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith($entityManager, 'doctrine/orm');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Collector\DatabaseCollector');
    }

    function it_says_mongodb_is_disabled_and_should_not_be_enabled(
        Request $request,
        Response $response,
        $entityManager,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select(Argument::any())->shouldBeCalled();
        $queryBuilder->from(Argument::any(), Argument::any())->shouldBeCalled();
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleScalarResult()->willReturn(self::MYSQL_PRODUCT_VALUE_LIMIT - 1);

        $this->collect($request, $response);

        $this->isMongoDbEnabled()->shouldReturn(false);
        $this->requireMongoDb()->shouldReturn(false);
    }

    function it_says_mongodb_is_disabled_and_should_be_enabled(
        Request $request,
        Response $response,
        $entityManager,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select(Argument::any())->shouldBeCalled();
        $queryBuilder->from(Argument::any(), Argument::any())->shouldBeCalled();
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleScalarResult()->willReturn(self::MYSQL_PRODUCT_VALUE_LIMIT + 1);

        $this->collect($request, $response);

        $this->isMongoDbEnabled()->shouldReturn(false);
        $this->requireMongoDb()->shouldReturn(true);
    }

    function it_says_mongodb_is_enabled(
        Request $request,
        Response $response,
        $entityManager,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $this->beConstructedWith($entityManager, 'doctrine/mongodb-odm');
        $entityManager->createQueryBuilder()->willReturn($queryBuilder);

        $this->collect($request, $response);

        $this->isMongoDbEnabled()->shouldReturn(true);
        $this->requireMongoDb()->shouldReturn(false);
    }
}
