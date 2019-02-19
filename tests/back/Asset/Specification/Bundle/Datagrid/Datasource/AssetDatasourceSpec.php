<?php

namespace Specification\Akeneo\Asset\Bundle\Datagrid\Datasource;

use Akeneo\Asset\Component\Model\Asset;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;

class AssetDatasourceSpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        HydratorInterface $hydrator
    ) {
        $this->beConstructedWith($entityManager, $hydrator);
    }

    function it_is_an_hydrator()
    {
        $this->shouldImplement(DatasourceInterface::class);
    }

    function it_returns_results_with_locale(
        $entityManager,
        $hydrator,
        DatagridInterface $configuration,
        AssetRepositoryInterface $assetRepository,
        QueryBuilder $qb
    ) {
        $results = [
            new ResultRecord([]),
            new ResultRecord([]),
        ];
        $config = [
            'entity'            => Asset::class,
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
