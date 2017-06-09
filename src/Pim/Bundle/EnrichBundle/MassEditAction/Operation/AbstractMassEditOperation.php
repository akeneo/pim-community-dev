<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * A basic implementation of the MassEditOperation.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditOperation implements
    MassEditOperationInterface,
    BatchableOperationInterface
{
    /** @var array */
    protected $filters;

    /** @var array */
    protected $actions;

    /** @var string The background job code to launch */
    protected $jobInstanceCode;

    /**
     * @param string $jobInstanceCode
     */
    public function __construct($jobInstanceCode)
    {
        $this->filters = [];
        $this->actions = [];
        $this->jobInstanceCode = $jobInstanceCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchConfig()
    {
        return [
            'filters' => $this->getFilters(),
            'actions' => $this->getActions(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getJobInstanceCode()
    {
        return $this->jobInstanceCode;
    }
}
