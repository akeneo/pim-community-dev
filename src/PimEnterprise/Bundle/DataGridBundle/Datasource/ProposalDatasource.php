<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ParameterizableInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * PIM datasource for proposals
 * Allow to prepare query builder from repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalDatasource implements DatasourceInterface/*, ParameterizableInterface*/
{
    /** @var string */
    const TYPE = 'pim_proposal';

    /** @var ObjectManager */
    protected $om;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var HydratorInterface */
    protected $hydrator;

    /** @var array grid configuration */
    protected $configuration;

    /** @var ObjectRepository $repository */
    protected $repository;

    /** @var QueryBuilder */
    protected $qb;

    /**
     * Constructor
     *
     * @param ObjectManager     $om
     * @param AclHelper         $aclHelper
     * @param HydratorInterface $hydrator
     */
    public function __construct(ObjectManager $om, AclHelper $aclHelper, HydratorInterface $hydrator)
    {
        $this->om        = $om;
        $this->aclHelper = $aclHelper; //TODO: Remove
        $this->hydrator  = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->configuration = $config;

        if (isset($config['repository_method']) && $method = $config['repository_method']) {
            $this->qb = $this->getRepository()->$method();
        } else {
            $this->qb = $this->getRepository()->createQueryBuilder('p');
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
     * Get query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    //     /**
    //      * {@inheritdoc}
    //      */
    //     public function getParameters()
    //     {
    //         return $this->parameters;
    //     }

    //     /**
    //      * {@inheritdoc}
    //      */
    //     public function setParameters($parameters)
    //     {
    //         $this->parameters = $parameters;
    //         $this->qb->setParameters($parameters);

    //         return $this;
    //     }

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
        if (!$this->repository) {
            $this->repository = $this->om->getRepository($this->getConfiguration('entity'));
        }

        return $this->repository;
    }

    /**
     * Get configuration
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws \LogicException
     * @throws \Exception
     */
    protected function getConfiguration($key)
    {
        if (!$this->configuration) {
            throw new \LogicException('Datasource is not yet built. You need to call process method before');
        }

        if (!isset($this->configuration[$key])) {
            throw new \Exception(sprintf('"%s" expects to be configured with "%s"', get_class($this), $key));
        }

        return $this->configuration[$key];
    }
}
