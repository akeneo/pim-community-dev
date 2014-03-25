<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

/**
 * Overriding of boolean filter to filter by the product completeness
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends BooleanFilter
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface       $factory
     * @param FilterUtility              $util
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        ProductRepositoryInterface $repository
    ) {
        parent::__construct($factory, $util);

        $this->repository = $repository;
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

        $qb = $ds->getQueryBuilder();

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $operator = '=';
                break;
            case BooleanFilterType::TYPE_NO:
            default:
                $operator = '<';
                break;
        }

        $this->repository->applyFilterByField($qb, 'completeness', '100', $operator);

        return true;
    }
}
