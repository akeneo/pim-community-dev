<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;

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
     * @var AssociationTypeRepository
     */
    protected $assocTypeRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface      $factory
     * @param FilterUtility             $util
     * @param RequestParameters         $requestParams
     * @param AssociationTypeRepository $repo
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParameters $requestParams,
        AssociationTypeRepository $repo
    ) {
        parent::__construct($factory, $util);
        $this->requestParams = $requestParams;
        $this->assocTypeRepository = $repo;
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

        $associationType = $this->getAssociationType($this->requestParams);
        $product         = $this->getCurrentProduct($this->requestParams);
        $productIds      = $this->getAssociatedProductIds($product, $associationType);
        $operator = ($data['value'] === BooleanFilterType::TYPE_YES) ? 'IN' : 'NOT IN';

        $qb = $ds->getQueryBuilder();
        $repository = $this->util->getProductRepository();
        $pqb = $repository->getProductQueryBuilder($qb);
        $pqb->addFieldFilter('id', $operator, $productIds);

        return true;
    }

    /**
     * @param RequestParameters $params
     *
     * @return AssociationType
     */
    protected function getAssociationType(RequestParameters $params)
    {
        $associationTypeId = $this->requestParams->get('associationType', null);
        if (!$associationTypeId) {
            throw new \LogicalException('The current association type must be configured');
        }

        $associationType = $this->assocTypeRepository->findOneBy(['id' => $associationTypeId]);

        return $associationType;
    }

    /**
     * @param RequestParameters $params
     *
     * @return AbstractProduct
     */
    protected function getCurrentProduct(RequestParameters $params)
    {
        $productId = $this->requestParams->get('product', null);
        if (!$productId) {
            throw new \LogicalException('The current product type must be configured');
        }
        $product = $this->util->getProductManager()->find($productId);

        return $product;
    }

    /**
     * @param AbstractProduct $product
     * @param AssociationType $type
     *
     * @return array
     */
    protected function getAssociatedProductIds(AbstractProduct $product, AssociationType $type)
    {
        $association = $product->getAssociationForType($type);
        $products    = $association->getProducts();
        $productIds  = [];
        foreach ($products as $product) {
            $productIds[]= $product->getId();
        }

        return $productIds;
    }
}
