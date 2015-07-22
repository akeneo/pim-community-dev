<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface');
    }

    function let(EntityManager $em, Connection $connection) {
        $classMetadata = new ClassMetadata('PimEnterprise\Component\ProductAsset\Model\Asset');
        $classMetadata->mapField([
            'fieldName' => 'sortOrder',
            'type' => 'integer',
        ]);
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_finds_the_product_assets_for_an_empty_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'pa.id as id, CONCAT(\'[\', pa.code, \']\') as text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('pa')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('pa.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('pa.code')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch();
    }

    function it_finds_the_product_assets_for_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'pa.id as id, CONCAT(\'[\', pa.code, \']\') as text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('pa')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('pa.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('pa.code')->willReturn($qb);
        $qb->andWhere('pa.code LIKE :search')->willReturn($qb);
        $qb->setParameter('search', '%my-search%')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch('my-search');
    }

    function it_finds_the_product_assets_third_page_of_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'pa.id as id, CONCAT(\'[\', pa.code, \']\') as text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('pa')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('pa.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('pa.code')->willReturn($qb);
        $qb->andWhere('pa.code LIKE :search')->willReturn($qb);
        $qb->setParameter('search', '%my-search%')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();
        $qb->setMaxResults(15)->willReturn($qb);
        $qb->setFirstResult(30)->willReturn($qb);

        $this->findBySearch('my-search', ['limit' => 15, 'page' => 3]);
    }

    function it_creates_asset_query_builder($em, QueryBuilder $qb)
    {
        $em->createQueryBuilder()->willReturn($qb);

        $qb->select('asset')->willReturn($qb);
        $qb->from('PimEnterprise\Component\ProductAsset\Model\Asset', 'asset', 'asset.id')->willReturn($qb);

        $this->createAssetDatagridQueryBuilder([]);
    }
}
