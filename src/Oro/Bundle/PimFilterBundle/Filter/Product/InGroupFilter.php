<?php

namespace Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectCodeResolver;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Product in group filter (used by group products grid)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InGroupFilter extends BooleanFilter
{
    /** @var RequestParametersExtractorInterface */
    protected $extractor;

    /** @var ObjectCodeResolver */
    protected $codeResolver;

    /**
     * Constructor
     *
     * @param FormFactoryInterface                $factory
     * @param FilterUtility                       $util
     * @param RequestParametersExtractorInterface $extractor
     * @param ObjectCodeResolver                  $codeResolver
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParametersExtractorInterface $extractor,
        ObjectCodeResolver $codeResolver
    ) {
        parent::__construct($factory, $util);
        $this->extractor = $extractor;
        $this->codeResolver = $codeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $groupId = $this->extractor->getDatagridParameter('currentGroup');
        if (!$groupId) {
            throw new \LogicException('The current product group must be configured');
        }

        $groupCodes = $this->codeResolver->getCodesFromIds('group', [$groupId]);

        $operator = ($data['value'] === BooleanFilterType::TYPE_YES) ? 'IN' : 'NOT IN';
        $this->util->applyFilter($ds, 'groups', $operator, $groupCodes);

        return true;
    }
}
