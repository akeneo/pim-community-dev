<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Product is associated filter (used by association product grid)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsAssociatedFilter extends BooleanFilter
{
    /** @var RequestParametersExtractorInterface */
    protected $extractor;

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /** @var ProductManager */
    protected $manager;

    /**
     * Constructor
     *
     * @param FormFactoryInterface                $factory
     * @param FilterUtility                       $util
     * @param RequestParametersExtractorInterface $extractor
     * @param AssociationTypeRepositoryInterface  $repo
     * @param ProductManager                      $manager
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParametersExtractorInterface $extractor,
        AssociationTypeRepositoryInterface $repo,
        ProductManager $manager
    ) {
        parent::__construct($factory, $util);
        $this->assocTypeRepository = $repo;
        $this->extractor = $extractor;
        $this->manager = $manager;
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

        $this->util->applyFilter($ds, 'id', $operator, $productIds);

        return true;
    }

    /**
     * @return \Pim\Component\Catalog\Model\AssociationTypeInterface
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
     * @return \Pim\Component\Catalog\Model\ProductInterface
     */
    protected function getCurrentProduct()
    {
        $productId = $this->extractor->getDatagridParameter('product');
        if (!$productId) {
            throw new \LogicException('The current product type must be configured');
        }
        $product = $this->manager->find($productId);

        return $product;
    }

    /**
     * @param \Pim\Component\Catalog\Model\ProductInterface         $product
     * @param \Pim\Component\Catalog\Model\AssociationTypeInterface $type
     *
     * @return array
     */
    protected function getAssociatedProductIds(ProductInterface $product, AssociationTypeInterface $type)
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
