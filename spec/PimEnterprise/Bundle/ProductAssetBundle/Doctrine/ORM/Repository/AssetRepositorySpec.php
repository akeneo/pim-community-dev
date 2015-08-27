<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
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
        $select = 'asset.id as id, CONCAT(\'[\', asset.code, \']\') as text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('asset')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('asset.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('asset.code')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch();
    }

    function it_finds_the_product_assets_for_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'asset.id as id, CONCAT(\'[\', asset.code, \']\') as text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('asset')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('asset.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('asset.code')->willReturn($qb);
        $qb->andWhere('asset.code LIKE :search')->willReturn($qb);
        $qb->setParameter('search', '%my-search%')->willReturn($qb);

        $qb->getQuery()->willReturn($query);

        $query->getArrayResult()->shouldBeCalled();

        $this->findBySearch('my-search');
    }

    function it_finds_the_product_assets_third_page_of_a_search($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $select = 'asset.id as id, CONCAT(\'[\', asset.code, \']\') as text';

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('asset')->willReturn($qb);
        $qb->select($select)->willReturn($qb);
        $qb->from(Argument::any(), Argument::any(), Argument::any())->willReturn($qb);
        $qb->orderBy('asset.sortOrder', 'DESC')->willReturn($qb);
        $qb->addOrderBy('asset.code')->willReturn($qb);
        $qb->andWhere('asset.code LIKE :search')->willReturn($qb);
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
        $qb->groupBy('asset.id')->willReturn($qb);

        $this->createAssetDatagridQueryBuilder([]);
    }

    function it_finds_all_assets_by_end_of_use_delay(
        $em,
        QueryBuilder $qb,
        AbstractQuery $query,
        AssetInterface $asset,
        AssetInterface $asset2
    ) {
        $now = new \DateTime('2015-08-10');
        $em->createQueryBuilder()->willReturn($qb);

        $qb->select('asset')->willReturn($qb);
        $qb->from('PimEnterprise\Component\ProductAsset\Model\Asset', 'asset')->willReturn($qb);
        $qb->where(':endOfUse1 < asset.endOfUseAt')->willReturn($qb);
        $qb->andWhere('asset.endOfUseAt < :endOfUse2')->willReturn($qb);
        $qb->setParameter(':endOfUse1', '2015-08-15 0:00:00')->willReturn($qb);
        $qb->setParameter(':endOfUse2', '2015-08-15 23:59:59')->willReturn($qb);

        $qb->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([$asset, $asset2]);

        $this->findExpiringAssets($now, 5)->shouldReturn([$asset, $asset2]);
    }
}
