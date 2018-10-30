<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductModelRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;

class ProductModelRepositorySpec extends ObjectBehavior
{
    function let(EntityManagerInterface $em, ClassMetadata $class)
    {
        $class->name = ProductModel::class;
        $this->beConstructedWith($em, $class);
    }

    function it_is_a_product_model_repository()
    {
        $this->shouldHaveType(ProductModelRepository::class);
        $this->shouldImplement(ProductModelRepositoryInterface::class);
    }

    function it_is_an_object_repository()
    {
        $this->shouldImplement(ObjectRepository::class);
    }

    function it_is_an_identifiable_object_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_cursable_repository()
    {
        $this->shouldImplement(CursorableRepositoryInterface::class);
    }

    function it_returns_the_product_model_identifier_property()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_product_model_by_its_identifier(
        $em,
        ProductModelInterface $productModel,
        UnitOfWork $uow,
        EntityPersister $persister
    ) {
        $productModel->getCode()->willReturn('foobar');
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityPersister(ProductModel::class)->willReturn($persister);
        $persister->load(['code' => 'foobar'], null, null, array(), null, 1, null)->willReturn($productModel);

        $this->findOneByIdentifier('foobar')->shouldReturn($productModel);
    }

    function it_finds_an_array_of_product_model_by_their_identifiers(
        $em,
        ProductModelInterface $fooModel,
        ProductModelInterface $barModel,
        QueryBuilder $qb,
        AbstractQuery $query
    ) {
        $fooModel->getCode()->willReturn('foo');
        $barModel->getCode()->willReturn('bar');

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('pm')->willReturn($qb);
        $qb->from(ProductModel::class, 'pm', null)->willReturn($qb);
        $qb->where('pm.code IN (:codes)')->willReturn($qb);
        $qb->setParameter('codes', ['foo', 'bar'])->willReturn($qb);

        $qb->getQuery()->willReturn($query);
        $query->execute()->willReturn([$fooModel, $barModel]);

        $this->getItemsFromIdentifiers(['foo', 'bar'])->shouldReturn([$fooModel, $barModel]);
    }
}
