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

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product proposal datasource, executes elasticsearch query
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductProposalDatasource implements DatasourceInterface, ParameterizableInterface
{
    /** @var ProductQueryBuilderInterface */
    protected $pqb;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var QueryBuilder|SearchQueryBuilder */
    private $qb;

    /** @var array */
    private $configuration;

    /** @var array */
    private $parameters = [];

    /** @var MassActionRepositoryInterface */
    private $massActionRepository;

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
     * {@inheritdoc}
     */
    public function setMassActionRepository(MassActionRepositoryInterface $massActionRepository)
    {
        $this->massActionRepository = $massActionRepository;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    public function getProductQueryBuilder()
    {
        return $this->pqb;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * @param string $method the query builder creation method
     * @param array  $config the query builder creation config
     *
     * @return ProductProposalDatasource
     * @throws \Exception
     */
    private function initializeQueryBuilder($method, array $config = [])
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
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
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


    /**
     * {@inheritdoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->configuration = $config;
        $queryBuilderConfig = [];
        if (isset($config['repository_method']) && $method = $config['repository_method']) {
            if (isset($config[ContextConfigurator::REPOSITORY_PARAMETERS_KEY])) {
                $queryBuilderConfig = $config[ContextConfigurator::REPOSITORY_PARAMETERS_KEY];
            }
        } else {
            $method = 'createQueryBuilder';
        }
        $this->initializeQueryBuilder($method, $queryBuilderConfig);

        $grid->setDatasource(clone $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters($parameters)
    {
        $this->parameters += $parameters;

        if ($this->qb instanceof QueryBuilder) {
            $this->qb->setParameters($this->parameters);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMassActionRepository()
    {
        if (null === $this->massActionRepository) {
            return $this->getRepository();
        }

        return $this->massActionRepository;
    }

    /**
     * Get a configuration value
     *
     * @param string $key
     * @param bool   $isRequired
     *
     * @throws \LogicException
     * @throws \Exception
     *
     * @return mixed
     */
    private function getConfiguration($key, $isRequired = true)
    {
        if (!$this->configuration) {
            throw new \LogicException('Datasource is not yet built. You need to call process method before');
        }

        if ($isRequired && !isset($this->configuration[$key])) {
            throw new \Exception(sprintf('"%s" expects to be configured with "%s"', get_class($this), $key));
        }

        return isset($this->configuration[$key]) ? $this->configuration[$key] : null;
    }

    public function getRepository()
    {
        throw new \Exception("Not implemented as useless for this use case...");
    }

    public function setHydrator(HydratorInterface $hydrator)
    {
        throw new \Exception("Not implemented as useless for this use case...");
    }
}
