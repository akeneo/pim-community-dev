<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Product filter utility
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFilterUtility extends BaseFilterUtility
{
    /**
     * @var string
     */
    const PARENT_TYPE_KEY = 'parent_type';

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @param ProductManager $manager
     */
    public function __construct(ProductManager $manager)
    {
        $this->productManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getParamMap()
    {
        return [self::PARENT_TYPE_KEY => self::TYPE_KEY];
    }

    /**
     * @return ProductManager
     */
    public function getProductManager()
    {
        return $this->productManager;
    }

    /**
     * @return ProductRepositoryInterface
     */
    public function getProductRepository()
    {
        return $this->productManager->getProductRepository();
    }

    /**
     * @param string $code
     *
     * @return AbstractAttribute
     */
    public function getAttribute($code)
    {
        $attributeRepo = $this->productManager->getAttributeRepository();
        $attribute     = $attributeRepo->findOneByCode($code);

        return $attribute;
    }

    /**
     * Applies filter to query by attribute
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param mixed                            $value
     * @param string                           $operator
     */
    public function applyFilterByAttribute(FilterDatasourceAdapterInterface $ds, $field, $value, $operator)
    {
        $attribute = $this->getAttribute($field);
        $productQueryBuilder = $this->getProductRepository()->getProductQueryBuilder($qs->getQueryBuilder());
        if ($attribute) {
            $productQueryBuilder->addAttributeFilter($attribute, $operator, $value);
        } else {
            $productQueryBuilder->addFieldFilter($field, $operator, $value);
        }
    }
}
