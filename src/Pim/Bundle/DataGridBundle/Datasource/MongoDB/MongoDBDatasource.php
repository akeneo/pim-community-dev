<?php

namespace Pim\Bundle\DataGridBundle\Datasource\MongoDB;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ParameterizableInterface;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * MongoDB datasource
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MongoDBDatasource implements DatasourceInterface, ParameterizableInterface
{
    /** @staticvar string */
    const TYPE = 'mongodb';

    /** @staticvar string */
    const REPOSITORY_PARAMETERS_KEY = 'repository_parameters';

    /** @var Doctrine\ODM\MongoDB\Query\Builder */
    protected $qb;

    /** @var ObjectManager */
    protected $om;

    /** @var HydratorInterface */
    protected $hydrator;

    /** @var array grid configuration */
    protected $configuration;

    /** @var array */
    protected $parameters = array();

    /** @var ProductRepositoryInterface $repository */
    protected $repository;

    /**
     * @param ObjectManager     $om
     * @param HydratorInterface $hydrator
     */
    public function __construct(ObjectManager $om, HydratorInterface $hydrator)
    {
        $this->om       = $om;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->configuration = $config;

        if (isset($config['repository_method']) && $method = $config['repository_method']) {
            if (isset($config[static::REPOSITORY_PARAMETERS_KEY])) {
                $this->qb = $this->getRepository()->$method($config[static::REPOSITORY_PARAMETERS_KEY]);
            } else {
                $this->qb = $this->getRepository()->$method();
            }

            $this->qb = $this->getRepository()->$method();
        } else {
            $this->qb = $this->getRepository()->createQueryBuilder('o');
        }

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
     * Returns query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
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
        $this->parameters = $parameters;
        if (method_exists($this->qb, 'setParameters')) {
            $this->qb->setParameters($parameters);
        }

        return $this;
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
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->om->getRepository($this->getConfiguration('entity'));
        }

        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getMassActionRepository()
    {
        return $this->getRepository();
    }

    /**
     * Get configuration
     *
     * @param string  $key
     * @param boolean $isRequired
     *
     * @return mixed
     *
     * @throws \LogicException
     * @throws \Exception
     */
    protected function getConfiguration($key, $isRequired = true)
    {
        if (!$this->configuration) {
            throw new \LogicException('Datasource is not yet built. You need to call process method before');
        }

        if ($isRequired && !isset($this->configuration[$key])) {
            throw new \Exception(sprintf('"%s" expects to be configured with "%s"', get_class($this), $key));
        }

        return $this->configuration[$key];
    }
}
