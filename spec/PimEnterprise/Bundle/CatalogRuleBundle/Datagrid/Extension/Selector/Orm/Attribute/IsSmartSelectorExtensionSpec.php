<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Datagrid\Extension\Selector\Orm\Attribute;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;

class IsSmartSelectorExtensionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Attribute', 'Resource');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            'PimEnterprise\Bundle\CatalogRuleBundle\Datagrid\Extension\Selector\Orm\Attribute\IsSmartSelectorExtension'
        );
    }

    function it_is_a_datagrid_extension()
    {
        $this->shouldImplement('Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface');
    }

    function it_applies_only_to_the_attribute_grid(DatagridConfiguration $config)
    {
        $config->getName()->willReturn('foo');
        $this->isApplicable($config)->shouldReturn(false);

        $config->getName()->willReturn('attribute-grid');
        $this->isApplicable($config)->shouldReturn(true);
    }

    function it_joins_and_selects_the_smart_property_of_attributes(
        DatasourceInterface $ds,
        DatagridConfiguration $config,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);
        $qb->getRootAliases()->willReturn(['a']);

        $qb
            ->leftJoin(
                'Resource',
                'r',
                'WITH',
                'r.resourceId = a.id AND r.resourceName = :attributeClass'
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->setParameter('attributeClass', 'Attribute')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->addSelect('CASE WHEN r.resourceId IS NULL THEN false ELSE true END AS is_smart')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->groupBy('a.id')->shouldBeCalled();

        $this->visitDatasource($config, $ds);
    }
}
