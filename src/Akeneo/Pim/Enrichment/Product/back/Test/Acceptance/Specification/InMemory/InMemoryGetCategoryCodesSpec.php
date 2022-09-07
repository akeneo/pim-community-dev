<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetCategoryCodes;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class InMemoryGetCategoryCodesSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository)
    {
        $this->beConstructedWith($productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetCategoryCodes::class);
        $this->shouldImplement(GetCategoryCodes::class);
    }

    function it_returns_the_category_codes(ProductRepositoryInterface $productRepository)
    {
        $master = new Category();
        $master->setCode('master');
        $print = new Category();
        $print->setCode('print');

        $product1 = new Product();
        $product1->setIdentifier('id1');
        $product1->addCategory($master);
        $product1->addCategory($print);

        $product2 = new Product();
        $product2->setIdentifier('id2');
        $product2->addCategory($master);

        $product3 = new Product();
        $product3->setIdentifier('id3');

        $productRepository->findAll()->willReturn([$product1, $product2, $product3]);

        $this->fromProductIdentifiers([])->shouldReturn([]);
        $this->fromProductIdentifiers([
            ProductIdentifier::fromString('id1'),
            ProductIdentifier::fromString('ID2'),
            ProductIdentifier::fromString('id3'),
            ProductIdentifier::fromString('unknown'),
        ])->shouldReturn([
            'id1' => ['master', 'print'],
            'id2' => ['master'],
            'id3' => [],
        ]);
    }

    function it_returns_the_category_codes_by_uuid(ProductRepositoryInterface $productRepository)
    {
        $master = new Category();
        $master->setCode('master');
        $print = new Category();
        $print->setCode('print');

        $uuid1 = Uuid::uuid4();
        $product1 = new Product($uuid1->toString());
        $product1->setIdentifier('id1');
        $product1->addCategory($master);
        $product1->addCategory($print);

        $uuid2 = Uuid::uuid4();
        $product2 = new Product($uuid2->toString());
        $product2->setIdentifier('id2');
        $product2->addCategory($master);

        $uuid3 = Uuid::uuid4();
        $product3 = new Product($uuid3->toString());
        $product3->setIdentifier('id3');

        $productRepository->findAll()->willReturn([$product1, $product2, $product3]);

        $this->fromProductUuids([])->shouldReturn([]);
        $this->fromProductUuids([$uuid1, $uuid2, $uuid3, Uuid::uuid4()])
             ->shouldReturn([
                $uuid1->toString() => ['master', 'print'],
                $uuid2->toString() => ['master'],
                $uuid3->toString() => [],
            ]);
    }
}
