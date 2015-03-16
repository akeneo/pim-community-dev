<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * A basic implementation of the MassEditOperation
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditOperation implements MassEditOperationInterface
{
    /** @var array */
    protected $filters;

    /** @var array */
    protected $actions;

    /**
     * {@inheritdoc}
     */
    abstract public function getAlias();

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
}
