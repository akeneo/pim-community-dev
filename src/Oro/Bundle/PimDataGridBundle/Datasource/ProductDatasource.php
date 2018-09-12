<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriberConfiguration;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product datasource, executes elasticsearch query
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

    /** @var FilterEntityWithValuesSubscriber */
    protected $filterEntityWithValuesSubscriber;

    /**
     * @param ObjectManager                       $om
     * @param ProductQueryBuilderFactoryInterface $factory
     * @param NormalizerInterface                 $serializer
     * @param FilterEntityWithValuesSubscriber    $filterEntityWithValuesSubscriber
     */
    public function __construct(
        ObjectManager $om,
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $serializer,
        FilterEntityWithValuesSubscriber $filterEntityWithValuesSubscriber
    ) {
        $this->om = $om;
        $this->factory = $factory;
        $this->normalizer = $serializer;
        $this->filterEntityWithValuesSubscriber = $filterEntityWithValuesSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $attributeIdsToDisplay = $this->getConfiguration('displayed_attribute_ids');
        $attributes = $this->getConfiguration('attributes_configuration');
        $attributeCodesToFilter = $this->getAttributeCodesToFilter($attributeIdsToDisplay, $attributes);
        $this->filterEntityWithValuesSubscriber->configure(
            FilterEntityWithValuesSubscriberConfiguration::filterEntityValues($attributeCodesToFilter)
        );

        $entitiesWithValues = $this->pqb->execute();
        $context = [
            'locales'             => [$this->getConfiguration('locale_code')],
            'channels'            => [$this->getConfiguration('scope_code')],
            'data_locale'         => $this->getParameters()['dataLocale'],
            'association_type_id' => $this->getConfiguration('association_type_id', false),
            'current_group_id'    => $this->getConfiguration('current_group_id', false),
        ];
        $rows = ['data' => []];

        foreach ($entitiesWithValues as $entityWithValue) {
            $normalizedItem = $this->normalizeEntityWithValues($entityWithValue, $context);
            $rows['data'][] = new ResultRecord($normalizedItem);
        }
        $rows['totalRecords'] = $entitiesWithValues->count();

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
        $factoryConfig['limit'] = (int) $this->getConfiguration(PagerExtension::PER_PAGE_PARAM);
        $factoryConfig['from'] = null !== $this->getConfiguration('from', false) ?
            (int) $this->getConfiguration('from', false) : 0;

        $this->pqb = $this->factory->create($factoryConfig);
        $this->qb = $this->pqb->getQueryBuilder();

        return $this;
    }

    /**
     * Normalizes an entity with values with the complete set of fields required to show it.
     *
     * @param EntityWithValuesInterface $item
     * @param array                     $context
     *
     * @return array
     */
    private function normalizeEntityWithValues(EntityWithValuesInterface $item, array $context): array
    {
        $defaultNormalizedItem = [
            'id'               => $item->getId(),
            'dataLocale'       => $this->getParameters()['dataLocale'],
            'family'           => null,
            'values'           => [],
            'created'          => null,
            'updated'          => null,
            'label'            => null,
            'image'            => null,
            'groups'           => null,
            'enabled'          => null,
            'completeness'     => null,
            'variant_products' => null,
            'document_type'    => null,
        ];

        $normalizedItem = array_merge(
            $defaultNormalizedItem,
            $this->normalizer->normalize($item, 'datagrid', $context)
        );

        return $normalizedItem;
    }

    /**
     * @param array $attributeIdsToDisplay
     * @param array $attributes
     *
     * @return array array of attribute codes
     */
    private function getAttributeCodesToFilter(array $attributeIdsToDisplay, array $attributes): array
    {
        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            if (in_array($attribute['id'], $attributeIdsToDisplay)) {
                $attributeCodes[] = $attribute['code'];
            }
        }

        return $attributeCodes;
    }
}
