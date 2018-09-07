<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyDatasource implements DatasourceInterface, ParameterizableInterface
{
    /** @var DatagridRepositoryInterface */
    protected $repository;

    /** @var MassActionRepositoryInterface */
    protected $massRepository;

    /** @var QueryBuilder */
    protected $qb;

    /** @var HydratorInterface */
    protected $hydrator;

    /** @var array */
    protected $parameters = [];

    /**
     * @param DatagridRepositoryInterface   $repository
     * @param MassActionRepositoryInterface $massRepository
     * @param HydratorInterface             $hydrator
     */
    public function __construct(
        DatagridRepositoryInterface $repository,
        MassActionRepositoryInterface $massRepository,
        HydratorInterface $hydrator
    ) {
        $this->repository = $repository;
        $this->massRepository = $massRepository;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->qb = $this->repository->createDatagridQueryBuilder();
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
        throw new \LogicException("No need to implement this method, design flaw in interface!");
    }

    /**
     * {@inheritdoc}
     */
    public function getMassActionRepository()
    {
        return $this->massRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setMassActionRepository(MassActionRepositoryInterface $massActionRepository)
    {
        throw new \LogicException("No need to implement this method, design flaw in interface!");
    }

    /**
     * {@inheritdoc}
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }
}
