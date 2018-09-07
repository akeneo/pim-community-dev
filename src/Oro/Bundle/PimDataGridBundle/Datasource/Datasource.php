<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;

/**
 * Pim agnostic datasource
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated you should use RepositoryDatasource
 */
class Datasource implements DatasourceInterface, ParameterizableInterface
{
    /** @var QueryBuilder|SearchQueryBuilder */
    protected $qb;

    /** @var ObjectManager */
    protected $om;

    /** @var ObjectRepository */
    protected $repository;

    /** @var MassActionRepositoryInterface */
    protected $massActionRepository;

    /** @var HydratorInterface */
    protected $hydrator;

    /** @var array */
    protected $configuration;

    /** @var array */
    protected $parameters = [];

    /**
     * @param ObjectManager     $om
     * @param HydratorInterface $hydrator
     */
    public function __construct(ObjectManager $om, HydratorInterface $hydrator)
    {
        $this->om = $om;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function setMassActionRepository(MassActionRepositoryInterface $massActionRepository)
    {
        $this->massActionRepository = $massActionRepository;
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
    public function getResults()
    {
        return $this->hydrator->hydrate($this->qb);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        if (null === $this->repository) {
            $this->repository = $this->om->getRepository($this->getConfiguration('entity'));
        }

        return $this->repository;
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
     * {@inheritdoc}
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
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
    protected function getConfiguration($key, $isRequired = true)
    {
        if (!$this->configuration) {
            throw new \LogicException('Datasource is not yet built. You need to call process method before');
        }

        if ($isRequired && !isset($this->configuration[$key])) {
            throw new \Exception(sprintf('"%s" expects to be configured with "%s"', get_class($this), $key));
        }

        return isset($this->configuration[$key]) ? $this->configuration[$key] : null;
    }

    /**
     * @param string $method the query builder creation method
     * @param array  $config the query builder creation config
     *
     * @return Datasource
     */
    protected function initializeQueryBuilder($method, array $config = [])
    {
        $this->qb = $this->getRepository()->$method($config);

        return $this;
    }
}
