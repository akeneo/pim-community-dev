<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;
use Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product proposal datasource, executes elasticsearch query
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductProposalDatasource extends Datasource
{
    /** @var ProductQueryBuilderInterface */
    protected $pqb;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ProductQueryBuilderFactoryInterface $factory
     * @param NormalizerInterface                 $serializer
     */
    public function __construct(ProductQueryBuilderFactoryInterface $factory, NormalizerInterface $serializer)
    {
        $this->factory = $factory;
        $this->normalizer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $entitiesWithValues = $this->pqb->execute();
        $rows = ['data' => []];

        foreach ($entitiesWithValues as $entityWithValue) {
            if ($entityWithValue->hasChanges()) {
                $normalizedItem = $this->normalizeEntityWithValues($entityWithValue);
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
     * @throws \Exception
     */
    protected function initializeQueryBuilder($method, array $config = [])
    {
        $factoryConfig['repository_parameters'] = $config;
        $factoryConfig['repository_method'] = $method;
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
     * @param EntityWithValuesDraftInterface $item
     *
     * @return array
     * @throws \Exception
     */
    private function normalizeEntityWithValues(EntityWithValuesDraftInterface $item): array
    {
        $defaultNormalizedItem = [
            'id'               => $item->getId(),
            'categories'       => null,
            'values'           => [],
            'created'          => null,
            'updated'          => null,
            'label'            => null,
            'changes'          => null,
            'document_type'    => null,
        ];

        $normalizedItem = array_merge($defaultNormalizedItem, $this->normalizer->normalize($item, 'datagrid'));

        return $normalizedItem;
    }
}
