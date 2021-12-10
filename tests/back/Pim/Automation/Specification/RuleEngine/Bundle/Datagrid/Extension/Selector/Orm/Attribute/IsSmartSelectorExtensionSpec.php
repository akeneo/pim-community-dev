<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Selector\Orm\Attribute;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Selector\Orm\Attribute\IsSmartSelectorExtension;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;

class IsSmartSelectorExtensionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Attribute', 'Resource');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsSmartSelectorExtension::class);
    }

    function it_is_a_datagrid_extension()
    {
        $this->shouldImplement(ExtensionVisitorInterface::class);
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
        QueryBuilder $qb,
        Expr $expr
    ) {
        $ds->getQueryBuilder()->willReturn($qb);
        $qb->getRootAliases()->willReturn(['a']);
        $qb->expr()->willReturn($expr);

        $expr->andX(null, null)->shouldBeCalled();
        $expr->eq("r.resourceId", "CASTASCHAR(a.id, utf8mb4_unicode_ci)")->shouldBeCalled();
        $expr->eq("r.resourceName", null)->shouldBeCalled();
        $expr->literal("Attribute")->shouldBeCalled();

        $qb
            ->leftJoin(
                'Resource',
                'r',
                'WITH',
                null
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->addSelect('CASE WHEN r.resourceId IS NULL THEN false ELSE true END AS is_smart')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->distinct(true)->shouldBeCalled();

        $this->visitDatasource($config, $ds);
    }
}
