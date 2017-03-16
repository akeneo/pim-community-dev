<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Selector\Orm\Product;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\ElasticSearchBundle\Query\QueryBuilder as EsQueryBuilder;


/**
 * @require Pim\Bundle\ElasticSearchBundle\Query\QueryBuilder
 */
class FamilySelectorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Extension\Selector\Orm\Product\FamilySelector');
    }

    function it_is_a_selector()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface');
    }

    function it_applies_a_selector(
        DatasourceInterface $datasource,
        DatagridConfiguration $configuration,
        EsQueryBuilder $esQueryBuilder,
        QueryBuilder $queryBuilder
    ) {
        $datasource->getQueryBuilder()->willReturn($esQueryBuilder);
        $qb = $datasource->getQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->getRootAlias()->willReturn('p');

        $configuration->offsetGetByPath('[source][locale_code]')->willReturn('en_US');

        $queryBuilder->leftJoin('p.family', 'family')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('family.translations', 'ft', 'WITH', 'ft.locale = :dataLocale')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(NULLIF(ft.label, \'\'), CONCAT(\'[\', family.code, \']\')) as familyLabel')->willReturn($queryBuilder);
        $queryBuilder->setParameter('dataLocale', 'en_US')->willReturn($queryBuilder);

        $this->apply($datasource, $configuration);
    }
}
