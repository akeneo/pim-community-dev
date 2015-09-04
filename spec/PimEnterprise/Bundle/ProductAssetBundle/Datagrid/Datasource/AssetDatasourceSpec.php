<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Datasource;

use Assetic\Asset\AssetInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;

class AssetDatasourceSpec extends ObjectBehavior
{
    function let(
        DatagridInterface $configuration,
        EntityManager $entityManager,
        HydratorInterface $hydrator
    ) {
        $this->beConstructedWith($entityManager, $hydrator);
    }

    function it_is_an_hydrator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface');
    }

    function it_returns_results_with_locale(
        $entityManager,
        $hydrator,
        $configuration,
        AssetRepositoryInterface $assetRepository,
        QueryBuilder $qb,
        AssetInterface $asset1,
        AssetInterface $asset2
    ) {
        $results = [
            new ResultRecord($asset1),
            new ResultRecord($asset2),
        ];
        $config = [
            'entity'            => '\\PimEnterprise\\Component\\ProductAsset\\Model\\Asset',
            'repository_method' => 'createAssetDatagridQueryBuilder',
            'locale_code'       => 'fr_FR',
        ];

        $qb->getParameters()->willReturn([]);
        $qb->getDQL()->willReturn('DQL');
        $qb->setParameters([])->shouldBeCalled();
        $assetRepository->createAssetDatagridQueryBuilder([])->willReturn($qb);
        $entityManager->getRepository($config['entity'])->willReturn($assetRepository);
        $this->process($configuration, $config);

        $hydrator->hydrate($qb, ['dataLocale' => 'fr_FR'])->willReturn($results);
        $this->getResults()->shouldReturn($results);
    }
}
