<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

class QualityScoreFilterSpec extends ObjectBehavior
{
    public function let(FormFactoryInterface $formFactory, FilterUtility $filterUtility)
    {
        $this->beConstructedWith($formFactory, $filterUtility);
    }

    public function it_applies_the_quality_score_filter(
        $filterUtility,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $filterUtility->applyFilter($filterDatasource, 'data_quality_insights_score', 'IN', [1, 3])->shouldBeCalled();

        $this->apply($filterDatasource, ['value' => [1, 3]]);
    }

    public function it_does_not_apply_quality_score_filter_when_the_filter_values_are_empty(
        $filterUtility,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $filterUtility->applyFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($filterDatasource, ['value' => []]);
    }

    public function it_does_not_apply_quality_score_filter_when_the_filter_values_are_not_an_array(
        $filterUtility,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $filterUtility->applyFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($filterDatasource, ['value' => 1]);
    }
}
