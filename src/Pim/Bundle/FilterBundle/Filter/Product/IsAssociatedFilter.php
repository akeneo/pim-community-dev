<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;

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
     * @var RequestParametersExtractorInterface
     */
    protected $extractor;

    /**
     * @var AssociationTypeRepository
     */
    protected $assocTypeRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface                $factory
     * @param FilterUtility                       $util
     * @param RequestParametersExtractorInterface $extractor
     * @param AssociationTypeRepository           $repo
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParametersExtractorInterface $extractor,
        AssociationTypeRepository $repo
    ) {
        parent::__construct($factory, $util);
        $this->assocTypeRepository = $repo;
        $this->extractor = $extractor;
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

        $associationType = $this->getAssociationType();
        $product         = $this->getCurrentProduct();
        $productIds      = $this->getAssociatedProductIds($product, $associationType);
        $operator = ($data['value'] === BooleanFilterType::TYPE_YES) ? 'IN' : 'NOT IN';

        $qb = $ds->getQueryBuilder();
        $repository = $this->util->getProductRepository();
        $pqb = $repository->getProductQueryBuilder($qb);
        $pqb->addFieldFilter('id', $operator, $productIds);

        return true;
    }

    /**
     * @return AssociationType
     */
    protected function getAssociationType()
    {
        $params = $this->extractor->getDatagridParameter(RequestParameters::ADDITIONAL_PARAMETERS, []);
        $associationTypeId = isset($params['associationType']) ? $params['associationType'] : null;

        if (!$associationTypeId) {
            $associationTypeId = $this->extractor->getDatagridParameter('associationType');
        }

        if (!$associationTypeId) {
            throw new \LogicException('The current association type must be configured');
        }

        $associationType = $this->assocTypeRepository->findOneBy(['id' => $associationTypeId]);

        return $associationType;
    }

    /**
     * @return AbstractProduct
     */
    protected function getCurrentProduct()
    {
        $productId = $this->extractor->getDatagridParameter('product');
        if (!$productId) {
            throw new \LogicException('The current product type must be configured');
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
        $productIds  = [];
        $association = $product->getAssociationForType($type);

        if ($association) {
            foreach ($association->getProducts() as $product) {
                $productIds[] = $product->getId();
            }
        }

        return $productIds ?: [0];
    }
}
