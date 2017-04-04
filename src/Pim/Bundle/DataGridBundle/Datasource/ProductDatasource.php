<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product datasource, execute elasticsearch query
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDatasource extends Datasource
{
    /** @var ProductQueryBuilderInterface */
    protected $pqb;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ObjectManager                       $om
     * @param HydratorInterface                   $hydrator
     * @param ProductQueryBuilderFactoryInterface $factory
     * @param NormalizerInterface                 $normalizer
     */
    public function __construct(
        ObjectManager $om,
        HydratorInterface $hydrator,
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $normalizer
    ) {
        parent::__construct($om, $hydrator);

        $this->factory = $factory;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $options = [
            'locale_code'              => $this->getConfiguration('locale_code'),
            'scope_code'               => $this->getConfiguration('scope_code'),
            'attributes_configuration' => $this->getConfiguration('attributes_configuration'),
            'current_group_id'         => $this->getConfiguration('current_group_id', false),
            'association_type_id'      => $this->getConfiguration('association_type_id', false),
            'current_product'          => $this->getConfiguration('current_product', false)
        ];

        if (method_exists($this->qb, 'setParameters')) {
            QueryBuilderUtility::removeExtraParameters($this->qb);
        }

        $productCursor = $this->pqb->execute();
        $context = ['locale' => $options['locale_code'], 'channel' => $options['scope_code']];
        $rows = ['totalRecords' => $productCursor->count()];

        foreach ($productCursor as $product) {
            $normalizedProduct = array_merge(
                $this->normalizer->normalize($product, 'internal_api', $context),
                ['id' => $product->getId(), 'dataLocale' => $this->getConfiguration('locale_code')]
            );
            $rows['data'][] = new ResultRecord($normalizedProduct);
        }

        return $rows;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    public function getProductQueryBuilder()
    {
        return $this->pqb;
    }

    /**
     * @param string $method the query builder creation method
     * @param array  $config the query builder creation config
     *
     * @return Datasource
     */
    protected function initializeQueryBuilder($method, array $config = [])
    {
        $factoryConfig['repository_parameters'] = $config;
        $factoryConfig['repository_method'] = $method;
        $factoryConfig['default_locale'] = $this->getConfiguration('locale_code');
        $factoryConfig['default_scope'] = $this->getConfiguration('scope_code');
        $factoryConfig['limit'] = $this->getConfiguration(ContextConfigurator::PRODUCTS_PER_PAGE);
        $factoryConfig['search_after'] = null; // TODO with TIP-664

        $this->pqb = $this->factory->create($factoryConfig);
        $this->qb = $this->pqb->getQueryBuilder();

        return $this;
    }
}
