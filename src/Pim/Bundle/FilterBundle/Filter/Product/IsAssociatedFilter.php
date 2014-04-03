<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;

/**
 * Product is associated filter (used by association product grid)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsAssociatedFilter extends BooleanFilter
{
    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param RequestParameters    $requestParams
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParameters $requestParams
    ) {
        parent::__construct($factory, $util);
        $this->requestParams = $requestParams;
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

        $associationTypeId = $this->requestParams->get('associationType', null);
        if (!$associationTypeId) {
            throw new \LogicalException('The current association type must be configured');
        }

        $productId = $this->requestParams->get('product', null);
        if (!$associationTypeId) {
            throw new \LogicalException('The current product type must be configured');
        }

        $operator = ($data['value'] === BooleanFilterType::TYPE_YES) ? 'IN' : 'NOT IN';
        $value = ['product' => $productId, 'associationType' => $associationTypeId];

        $qb = $ds->getQueryBuilder();
        $repository = $this->util->getProductRepository();
        $pqb = $repository->getProductQueryBuilder($qb);
        $pqb->addFieldFilter('is_associated', $operator, $value);

        return true;
    }
}
