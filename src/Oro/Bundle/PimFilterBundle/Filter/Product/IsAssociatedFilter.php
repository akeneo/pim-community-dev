<?php

namespace Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
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

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface                $factory
     * @param FilterUtility                       $util
     * @param RequestParametersExtractorInterface $extractor
     * @param AssociationTypeRepositoryInterface  $repo
     * @param ProductRepositoryInterface          $productRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParametersExtractorInterface $extractor,
        AssociationTypeRepositoryInterface $repo,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($factory, $util);
        $this->assocTypeRepository = $repo;
        $this->extractor = $extractor;
        $this->productRepository = $productRepository;
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
        $product = $this->getCurrentProduct();
        $productIds = $this->getAssociatedProductIds($product, $associationType);
        $operator = ($data['value'] === BooleanFilterType::TYPE_YES) ? 'IN' : 'NOT IN';

        $this->util->applyFilter($ds, 'id', $operator, $productIds);

        return true;
    }

    /**
     * @return AssociationTypeInterface
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
     * @return ProductInterface
     */
    protected function getCurrentProduct()
    {
        $productId = $this->extractor->getDatagridParameter('product');
        if (!$productId) {
            throw new \LogicException('The current product type must be configured');
        }
        $product = $this->productRepository->find($productId);

        return $product;
    }

    /**
     * @param ProductInterface         $product
     * @param AssociationTypeInterface $type
     *
     * @return array
     */
    protected function getAssociatedProductIds(ProductInterface $product, AssociationTypeInterface $type)
    {
        $productIds = [];
        $association = $product->getAssociationForType($type);

        if ($association) {
            foreach ($association->getProducts() as $product) {
                $productIds[] = (string) $product->getId();
            }
        }

        return $productIds ?: ['0'];
    }
}
