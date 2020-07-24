<?php

namespace spec\Akeneo\SharedCatalog\Controller;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\SharedCatalog\Query\FindProductIdentifiersQueryInterface;
use Akeneo\SharedCatalog\Query\FindSharedCatalogQueryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductListActionSpec extends ObjectBehavior
{
    function let(
        FindSharedCatalogQueryInterface $findSharedCatalogQuery,
        FindProductIdentifiersQueryInterface $findProductIdentifiersQuery
    ) {
        $this->beConstructedWith($findSharedCatalogQuery, $findProductIdentifiersQuery, 20);
    }

    public function it_throw_bad_request_when_catalog_does_not_exist(
        FindSharedCatalogQueryInterface $findSharedCatalogQuery
    ) {
        $findSharedCatalogQuery->find('unknown_catalog')->shouldBeCalled()->willReturn(null);

        $this->shouldThrow(new NotFoundHttpException('Catalog "unknown_catalog" does not exist'))
            ->during('__invoke', [new Request(), 'unknown_catalog']);
    }

    public function it_return_400_http_status_when_catalog_does_not_exist(
        FindSharedCatalogQueryInterface $findSharedCatalogQuery,
        FindProductIdentifiersQueryInterface $findProductIdentifiersQuery
    ) {
        $sharedCatalog = new SharedCatalog('shared_catalog_1', 'admin', [], [], []);
        $findSharedCatalogQuery->find('shared_catalog_1')->willReturn($sharedCatalog);

        $findProductIdentifiersQuery->find($sharedCatalog, [
            "search_after" => null,
            "limit" => 20
        ])
            ->shouldBeCalled()
            ->willThrow(new \InvalidArgumentException());

        $this->shouldThrow(new BadRequestHttpException())->during('__invoke', [new Request(), 'shared_catalog_1']);
    }
}
