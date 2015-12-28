<?php

namespace spec\Pim\Bundle\CatalogBundle\Collector;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Pim\Component\Catalog\Repository\ProductValueCounterRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DatabaseCollectorSpec extends ObjectBehavior
{
    /** @var int the max number of product value allowed before having to switch to MongoBD */
    const MYSQL_PRODUCT_VALUE_LIMIT = 5000000;

    function let(VersionProviderInterface $providerInterface, ProductValueCounterRepositoryInterface $repository)
    {
        $this->beConstructedWith($providerInterface, $repository, 'doctrine/orm');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Collector\DatabaseCollector');
    }

    function it_says_mongodb_is_disabled_and_should_not_be_enabled(
        Request $request,
        Response $response,
        $repository
    ) {
        $repository->count()->willReturn(self::MYSQL_PRODUCT_VALUE_LIMIT - 1);

        $this->collect($request, $response);

        $this->isMongoDbEnabled()->shouldReturn(false);
        $this->isMongoDbRequired()->shouldReturn(false);
    }

    function it_says_mongodb_is_disabled_and_should_be_enabled(
        Request $request,
        Response $response,
        $repository
    ) {
        $repository->count()->willReturn(self::MYSQL_PRODUCT_VALUE_LIMIT + 1);

        $this->collect($request, $response);

        $this->isMongoDbEnabled()->shouldReturn(false);
        $this->isMongoDbRequired()->shouldReturn(true);
    }

    function it_says_mongodb_is_enabled(
        Request $request,
        Response $response,
        $repository,
        $providerInterface
    ) {
        $this->beConstructedWith($providerInterface, $repository, 'doctrine/mongodb-odm');

        $this->collect($request, $response);

        $this->isMongoDbEnabled()->shouldReturn(true);
        $this->isMongoDbRequired()->shouldReturn(false);
    }
}
