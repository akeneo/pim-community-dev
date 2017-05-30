<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension;
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
     * @param ProductQueryBuilderFactoryInterface $factory
     * @param NormalizerInterface                 $normalizer
     */
    public function __construct(
        ObjectManager $om,
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $normalizer
    ) {
        $this->om = $om;
        $this->factory = $factory;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $productCursor = $this->pqb->execute();
        $context = [
            'locales'             => [$this->getConfiguration('locale_code')],
            'channels'            => [$this->getConfiguration('scope_code')],
            'data_locale'         => $this->getParameters()['dataLocale'],
            'association_type_id' => $this->getConfiguration('association_type_id', false),
            'current_group_id'    => $this->getConfiguration('current_group_id', false),
        ];
        $rows = ['totalRecords' => $productCursor->count(), 'data' => []];

        foreach ($productCursor as $product) {
            $normalizedProduct = array_merge(
                $this->normalizer->normalize($product, 'datagrid', $context),
                ['id' => $product->getId(), 'dataLocale' => $this->getParameters()['dataLocale']]
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
        $factoryConfig['limit'] = (int)$this->getConfiguration(PagerExtension::PER_PAGE_PARAM);
        $factoryConfig['from'] = null !== $this->getConfiguration('from', false) ?
            (int)$this->getConfiguration('from', false) : 0;

        $this->pqb = $this->factory->create($factoryConfig);
        $this->qb = $this->pqb->getQueryBuilder();

        return $this;
    }
}
