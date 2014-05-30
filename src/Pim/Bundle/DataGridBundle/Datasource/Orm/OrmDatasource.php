<?php

namespace Pim\Bundle\DataGridBundle\Datasource\Orm;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as OroOrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ParameterizableInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Basic PIM data source, allow to prepare query builder from repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmDatasource extends OroOrmDatasource implements DatasourceInterface, ParameterizableInterface
{
    /** @staticvar string */
    const TYPE = 'pim_orm';

    /** @staticvar string */
    const ENTITY_PATH = '[source][entity]';

    /** @var HydratorInterface */
    protected $hydrator;

    /** @var array */
    protected $parameters = array();

    /** @var array grid configuration */
    protected $configuration;

    /** @var EntityRepository $repository */
    protected $repository;

    /**
     * @param EntityManager     $em
     * @param AclHelper         $aclHelper
     * @param HydratorInterface $hydrator
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper, HydratorInterface $hydrator)
    {
        parent::__construct($em, $aclHelper);

        $this->hydrator = $hydrator;
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
        $this->qb->setParameters($parameters);

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
            $this->repository = $this->em->getRepository($this->getConfiguration('entity'));
        }

        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getMassActionRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository($this->getConfiguration('entity'));
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
