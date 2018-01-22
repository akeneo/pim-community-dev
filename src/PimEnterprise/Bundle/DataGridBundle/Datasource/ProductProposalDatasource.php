<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;
use Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product proposal datasource, executes elasticsearch query
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductProposalDatasource extends Datasource
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ProductQueryBuilderFactoryInterface $factory
     * @param NormalizerInterface                 $serializer
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $factory,
        NormalizerInterface $serializer
    ) {
        $this->factory = $factory;
        $this->normalizer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $entitiesWithValues = $this->pqb->execute();
        $context = [
            'locales'             => [$this->getConfiguration('locale_code')],
            'channels'            => [$this->getConfiguration('scope_code')],
            'data_locale'         => $this->getConfiguration('locale_code'),
        ];
        $rows = ['data' => []];

        foreach ($entitiesWithValues as $entityWithValue) {
            if($entityWithValue->hasChanges()){
                $normalizedItem = $this->normalizeEntityWithValues($entityWithValue, $context);
                $rows['data'][] = new ResultRecord($normalizedItem);
            }
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
     * @throws \Exception
     */
    private function normalizeEntityWithValues(EntityWithValuesInterface $item, array $context): array
    {
        $defaultNormalizedItem = [
            'id'               => $item->getId(),
            'dataLocale'       => $this->getConfiguration('locale_code'),
            'categories'       => null,
            'values'           => [],
            'created'          => null,
            'updated'          => null,
            'label'            => null,
            'changes'          => null,
            'document_type'    => null,
        ];

        $normalizedItem = array_merge(
            $defaultNormalizedItem,
            $this->normalizer->normalize($item, 'datagrid', $context)
        );

        return $normalizedItem;
    }
}
